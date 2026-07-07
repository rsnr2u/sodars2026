<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Services;

use App\Core\ValueObjects\Money;
use App\Core\ValueObjects\Currency;
use App\Platform\Accounting\ChartOfAccounts\LedgerAccount;
use App\Platform\Accounting\ChartOfAccounts\AccountType;
use App\Platform\Accounting\ChartOfAccounts\AccountingPeriod;
use App\Platform\Accounting\Posting\PostingEngine;
use App\Platform\Accounting\Posting\JournalData;
use App\Platform\Accounting\Posting\LedgerLineData;
use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Modules\Wallet\Domain\Entities\WalletTransaction;
use App\Modules\Wallet\Domain\Entities\Withdrawal;
use App\Modules\Wallet\Domain\Enums\TransactionType;
use App\Modules\Wallet\Domain\Enums\TransactionStatus;
use App\Modules\Wallet\Domain\Enums\WithdrawalStatus;
use App\Modules\Wallet\Domain\Enums\PostingStatus;
use App\Modules\Wallet\Domain\Enums\WalletState;
use App\Modules\Wallet\Application\Services\WalletLifecycleService;
use App\Platform\Identity\Application\Services\IdentityContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WalletService
{
    public function __construct(
        protected PostingEngine $postingEngine,
        protected WalletLifecycleService $lifecycleService
    ) {}

    /**
     * Create a new polymorphic wallet with linked general ledger account.
     */
    public function createWallet(Model $holder, string $walletType, string $currency = 'INR'): Wallet
    {
        return DB::transaction(function () use ($holder, $walletType, $currency) {
            $orgId = $holder->organization_id ?? IdentityContext::organizationId();

            // Find liability parent account
            $parent = LedgerAccount::where('code', '2000-LIABILITIES')->first();

            $accountCode = '2110-WALLET-' . strtoupper(Str::random(8));
            
            // Create a dedicated general ledger account
            $ledgerAccount = LedgerAccount::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'parent_account_id' => $parent?->id,
                'name' => "Wallet Account for " . class_basename($holder) . " #{$holder->id}",
                'code' => $accountCode,
                'type' => AccountType::Liability->value,
                'normal_balance' => 'credit',
                'is_control_account' => false,
                'allow_manual_posting' => true,
                'is_active' => true,
                'currency' => $currency,
            ]);

            // Generate unique human-readable wallet number
            $walletNumber = 'WAL-' . mt_rand(100000, 999999);
            while (Wallet::where('wallet_number', $walletNumber)->exists()) {
                $walletNumber = 'WAL-' . mt_rand(100000, 999999);
            }

            $wallet = Wallet::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'wallet_number' => $walletNumber,
                'holder_type' => $holder->getMorphClass(),
                'holder_id' => $holder->id,
                'ledger_account_id' => $ledgerAccount->id,
                'wallet_type' => $walletType,
                'currency' => $currency,
                'status' => WalletState::Active->value,
            ]);

            $this->lifecycleService->recordCreation($wallet);

            return $wallet;
        });
    }

    /**
     * Record dynamic deposit transaction.
     */
    public function deposit(Wallet $wallet, int $amountCents, string $reference, array $metadata = []): WalletTransaction
    {
        $this->ensurePeriodNotLocked();

        return DB::transaction(function () use ($wallet, $amountCents, $reference, $metadata) {
            $orgId = $wallet->organization_id;
            $bankAccount = LedgerAccount::where('code', '1110-BANK')->firstOrFail();
            $walletAccount = $wallet->ledgerAccount;

            $money = new Money($amountCents, new Currency($wallet->currency));

            $journalData = new JournalData(
                referenceNumber: 'DEP-' . strtoupper(Str::random(12)),
                narration: "Deposit of " . ($amountCents / 100) . " into wallet {$wallet->id}",
                journalType: 'wallet',
                sourceModule: 'Wallet',
                sourceId: $wallet->id,
                sourceType: 'Wallet',
                sourceEvent: 'wallet.deposited'
            );

            // Double entry lines
            $lines = [
                new LedgerLineData(
                    accountId: $bankAccount->id,
                    entryType: 'debit',
                    money: $money,
                    description: "Cash received for Deposit: {$reference}",
                    ledgerableType: Wallet::class,
                    ledgerableId: $wallet->id
                ),
                new LedgerLineData(
                    accountId: $walletAccount->id,
                    entryType: 'credit',
                    money: $money,
                    description: "Wallet credited for Deposit: {$reference}",
                    ledgerableType: Wallet::class,
                    ledgerableId: $wallet->id
                )
            ];

            // Enforce Ledger Integrity invariant
            $journal = $this->postingEngine->post($journalData, $lines);
            if (!$journal) {
                throw new \RuntimeException("Ledger journal posting failed. Rolling back.");
            }

            // Sequence & dynamic balance calculation
            $nextSequence = $this->getNextSequenceNumber($wallet->id);
            $txnRef = 'TXN-' . mt_rand(100000, 999999);
            while (WalletTransaction::where('transaction_reference', $txnRef)->exists()) {
                $txnRef = 'TXN-' . mt_rand(100000, 999999);
            }

            $currentBalance = $this->calculateDynamicBalance($wallet->id);

            // Create transaction in database bypass observer check (it is a fresh insert)
            $tx = WalletTransaction::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'wallet_id' => $wallet->id,
                'ledger_journal_id' => $journal->id,
                'amount_cents' => $amountCents,
                'running_balance_snapshot' => $currentBalance,
                'type' => TransactionType::Deposit->value,
                'status' => TransactionStatus::Completed->value,
                'posting_status' => PostingStatus::Posted->value,
                'reference_number' => $reference,
                'transaction_reference' => $txnRef,
                'sequence_number' => $nextSequence,
                'metadata' => $metadata,
            ]);

            // Update cached balance on wallet projection
            $wallet->update(['balance' => $currentBalance]);

            $eventData = [
                'transaction_id' => $tx->id,
                'wallet_id' => $wallet->id,
                'amount_cents' => $amountCents,
                'transaction_reference' => $txnRef,
            ];

            $this->lifecycleService->recordDeposit($wallet, $eventData);

            return $tx;
        });
    }

    /**
     * Request a withdrawal from the wallet.
     */
    public function requestWithdrawal(Wallet $wallet, int $amountCents, array $bankDetails): Withdrawal
    {
        return DB::transaction(function () use ($wallet, $amountCents, $bankDetails) {
            // Read aggregate authoritative balance inside database transaction (pessimistic locking equivalent)
            $balance = $this->calculateDynamicBalance($wallet->id);

            if ($balance < $amountCents) {
                throw new InvalidArgumentException("Insufficient wallet balance.");
            }

            // Generate withdrawal business number
            $withdrawalNumber = 'WDR-' . mt_rand(100000, 999999);
            while (Withdrawal::where('withdrawal_number', $withdrawalNumber)->exists()) {
                $withdrawalNumber = 'WDR-' . mt_rand(100000, 999999);
            }

            $withdrawal = Withdrawal::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $wallet->organization_id,
                'withdrawal_number' => $withdrawalNumber,
                'wallet_id' => $wallet->id,
                'amount_cents' => $amountCents,
                'bank_account_details' => $bankDetails,
                'status' => WithdrawalStatus::Requested->value,
            ]);

            $eventData = [
                'withdrawal_id' => $withdrawal->id,
                'wallet_id' => $wallet->id,
                'amount_cents' => $amountCents,
            ];

            $this->lifecycleService->recordWithdrawalRequested($wallet, $eventData);

            return $withdrawal;
        });
    }

    /**
     * Process / Approve and Complete a withdrawal request.
     */
    public function completeWithdrawal(Withdrawal $withdrawal, string $payoutReference): void
    {
        $this->ensurePeriodNotLocked();

        DB::transaction(function () use ($withdrawal, $payoutReference) {
            $withdrawal->refresh();

            if ($withdrawal->status !== WithdrawalStatus::Requested) {
                throw new InvalidArgumentException("Withdrawal is not in requested status.");
            }

            $wallet = $withdrawal->wallet;

            // Confirm dynamic balance inside transaction
            $balance = $this->calculateDynamicBalance($wallet->id);
            if ($balance < $withdrawal->amount_cents) {
                throw new InvalidArgumentException("Insufficient wallet balance at completion time.");
            }

            $walletAccount = $wallet->ledgerAccount;
            $bankAccount = LedgerAccount::where('code', '1110-BANK')->firstOrFail();

            $money = new Money($withdrawal->amount_cents, new Currency($wallet->currency));

            $journalData = new JournalData(
                referenceNumber: 'WTH-' . strtoupper(Str::random(12)),
                narration: "Withdrawal processing completed for request {$withdrawal->id}",
                journalType: 'withdrawal',
                sourceModule: 'Wallet',
                sourceId: $withdrawal->id,
                sourceType: 'Withdrawal',
                sourceEvent: 'withdrawal.completed'
            );

            // Double Entry lines
            $lines = [
                new LedgerLineData(
                    accountId: $walletAccount->id,
                    entryType: 'debit',
                    money: $money,
                    description: "Wallet payout debit.",
                    ledgerableType: Withdrawal::class,
                    ledgerableId: $withdrawal->id
                ),
                new LedgerLineData(
                    accountId: $bankAccount->id,
                    entryType: 'credit',
                    money: $money,
                    description: "Bank payout credit reference: {$payoutReference}",
                    ledgerableType: Withdrawal::class,
                    ledgerableId: $withdrawal->id
                )
            ];

            // Enforce Ledger Integrity invariant
            $journal = $this->postingEngine->post($journalData, $lines);
            if (!$journal) {
                throw new \RuntimeException("Ledger journal posting failed. Rolling back.");
            }

            $withdrawal->update([
                'status' => WithdrawalStatus::Completed->value,
                'payout_reference' => $payoutReference,
            ]);

            $nextSequence = $this->getNextSequenceNumber($wallet->id);
            $txnRef = 'TXN-' . mt_rand(100000, 999999);
            while (WalletTransaction::where('transaction_reference', $txnRef)->exists()) {
                $txnRef = 'TXN-' . mt_rand(100000, 999999);
            }

            $currentBalance = $this->calculateDynamicBalance($wallet->id);

            WalletTransaction::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $wallet->organization_id,
                'wallet_id' => $wallet->id,
                'ledger_journal_id' => $journal->id,
                'amount_cents' => $withdrawal->amount_cents,
                'running_balance_snapshot' => $currentBalance,
                'type' => TransactionType::Withdrawal->value,
                'status' => TransactionStatus::Completed->value,
                'posting_status' => PostingStatus::Posted->value,
                'reference_number' => $payoutReference,
                'transaction_reference' => $txnRef,
                'sequence_number' => $nextSequence,
            ]);

            $wallet->update(['balance' => $currentBalance]);

            $eventData = [
                'withdrawal_id' => $withdrawal->id,
                'wallet_id' => $wallet->id,
                'amount_cents' => $withdrawal->amount_cents,
                'payout_reference' => $payoutReference,
            ];

            $this->lifecycleService->recordWithdrawalCompleted($wallet, $eventData);
        });
    }

    /**
     * Reject a withdrawal request.
     */
    public function rejectWithdrawal(Withdrawal $withdrawal, string $reason): void
    {
        DB::transaction(function () use ($withdrawal, $reason) {
            $withdrawal->refresh();

            if ($withdrawal->status !== WithdrawalStatus::Requested) {
                throw new InvalidArgumentException("Withdrawal is not in requested status.");
            }

            $withdrawal->update([
                'status' => WithdrawalStatus::Rejected->value,
            ]);

            $eventData = [
                'withdrawal_id' => $withdrawal->id,
                'wallet_id' => $withdrawal->wallet_id,
                'reason' => $reason,
            ];

            $this->lifecycleService->recordWithdrawalRejected($withdrawal->wallet, $eventData);
        });
    }

    /**
     * Transfer funds from one wallet to another.
     */
    public function transfer(Wallet $fromWallet, Wallet $toWallet, int $amountCents, string $reference): void
    {
        $this->ensurePeriodNotLocked();

        DB::transaction(function () use ($fromWallet, $toWallet, $amountCents, $reference) {
            if ($fromWallet->currency !== $toWallet->currency) {
                throw new InvalidArgumentException("Currency mismatch across wallets.");
            }

            $fromBalance = $this->calculateDynamicBalance($fromWallet->id);

            if ($fromBalance < $amountCents) {
                throw new InvalidArgumentException("Insufficient funds for transfer.");
            }

            $money = new Money($amountCents, new Currency($fromWallet->currency));

            $journalData = new JournalData(
                referenceNumber: 'TRF-' . strtoupper(Str::random(12)),
                narration: "Internal transfer from wallet {$fromWallet->id} to {$toWallet->id}",
                journalType: 'transfer',
                sourceModule: 'Wallet',
                sourceId: $fromWallet->id,
                sourceType: 'WalletTransfer',
                sourceEvent: 'wallet.transferred'
            );

            // Double Entry lines
            $lines = [
                new LedgerLineData(
                    accountId: $fromWallet->ledgerAccount->id,
                    entryType: 'debit',
                    money: $money,
                    description: "Transfer debit payout to {$toWallet->id}",
                    ledgerableType: Wallet::class,
                    ledgerableId: $fromWallet->id
                ),
                new LedgerLineData(
                    accountId: $toWallet->ledgerAccount->id,
                    entryType: 'credit',
                    money: $money,
                    description: "Transfer credit deposit from {$fromWallet->id}",
                    ledgerableType: Wallet::class,
                    ledgerableId: $toWallet->id
                )
            ];

            // Enforce Ledger Integrity invariant
            $journal = $this->postingEngine->post($journalData, $lines);
            if (!$journal) {
                throw new \RuntimeException("Ledger journal posting failed. Rolling back.");
            }

            // Save snapshots for sender and receiver
            $fromBalanceSnap = $this->calculateDynamicBalance($fromWallet->id);
            $toBalanceSnap = $this->calculateDynamicBalance($toWallet->id);

            $txnRef1 = 'TXN-' . mt_rand(100000, 999999);
            while (WalletTransaction::where('transaction_reference', $txnRef1)->exists()) {
                $txnRef1 = 'TXN-' . mt_rand(100000, 999999);
            }
            $txnRef2 = 'TXN-' . mt_rand(100000, 999999);
            while (WalletTransaction::where('transaction_reference', $txnRef2)->exists()) {
                $txnRef2 = 'TXN-' . mt_rand(100000, 999999);
            }

            WalletTransaction::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $fromWallet->organization_id,
                'wallet_id' => $fromWallet->id,
                'ledger_journal_id' => $journal->id,
                'amount_cents' => $amountCents,
                'running_balance_snapshot' => $fromBalanceSnap,
                'type' => TransactionType::Transfer->value,
                'status' => TransactionStatus::Completed->value,
                'posting_status' => PostingStatus::Posted->value,
                'reference_number' => $reference,
                'transaction_reference' => $txnRef1,
                'sequence_number' => $this->getNextSequenceNumber($fromWallet->id),
            ]);

            WalletTransaction::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $toWallet->organization_id,
                'wallet_id' => $toWallet->id,
                'ledger_journal_id' => $journal->id,
                'amount_cents' => $amountCents,
                'running_balance_snapshot' => $toBalanceSnap,
                'type' => TransactionType::Transfer->value,
                'status' => TransactionStatus::Completed->value,
                'posting_status' => PostingStatus::Posted->value,
                'reference_number' => $reference,
                'transaction_reference' => $txnRef2,
                'sequence_number' => $this->getNextSequenceNumber($toWallet->id),
            ]);

            $fromWallet->update(['balance' => $fromBalanceSnap]);
            $toWallet->update(['balance' => $toBalanceSnap]);

            $eventData = [
                'from_wallet_id' => $fromWallet->id,
                'to_wallet_id' => $toWallet->id,
                'amount_cents' => $amountCents,
                'reference' => $reference,
            ];

            $this->lifecycleService->recordTransfer($fromWallet, $eventData);
        });
    }

    /**
     * Credit settlement from Settlement Paid event.
     */
    public function creditSettlement(Wallet $wallet, int $amountCents, string $settlementId): void
    {
        $this->ensurePeriodNotLocked();

        DB::transaction(function () use ($wallet, $amountCents, $settlementId) {
            // Find the provider settlement details to lock and reconcile references
            $settlement = \App\Modules\Finance\Domain\Entities\ProviderSettlement::findOrFail($settlementId);

            $settlementPayable = LedgerAccount::where('code', '2300-SETTLEMENT-PAYABLE')->firstOrFail();
            $walletAccount = $wallet->ledgerAccount;

            $money = new Money($amountCents, new Currency($wallet->currency));

            $journalData = new JournalData(
                referenceNumber: 'STL-' . strtoupper(Str::random(12)),
                narration: "Settlement payout auto-credited to wallet {$wallet->id}",
                journalType: 'settlement',
                sourceModule: 'Finance',
                sourceId: $settlementId,
                sourceType: 'ProviderSettlement',
                sourceEvent: 'settlement.paid'
            );

            // Double Entry lines
            $lines = [
                new LedgerLineData(
                    accountId: $settlementPayable->id,
                    entryType: 'debit',
                    money: $money,
                    description: "Payout settlement debit from payables.",
                    ledgerableType: Wallet::class,
                    ledgerableId: $wallet->id
                ),
                new LedgerLineData(
                    accountId: $walletAccount->id,
                    entryType: 'credit',
                    money: $money,
                    description: "Settlement payout credited to wallet.",
                    ledgerableType: Wallet::class,
                    ledgerableId: $wallet->id
                )
            ];

            // Enforce Ledger Integrity invariant
            $journal = $this->postingEngine->post($journalData, $lines);
            if (!$journal) {
                throw new \RuntimeException("Ledger journal posting failed. Rolling back.");
            }

            $currentBalance = $this->calculateDynamicBalance($wallet->id);

            $txnRef = 'TXN-' . mt_rand(100000, 999999);
            while (WalletTransaction::where('transaction_reference', $txnRef)->exists()) {
                $txnRef = 'TXN-' . mt_rand(100000, 999999);
            }

            WalletTransaction::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $wallet->organization_id,
                'wallet_id' => $wallet->id,
                'ledger_journal_id' => $journal->id,
                'amount_cents' => $amountCents,
                'running_balance_snapshot' => $currentBalance,
                'type' => TransactionType::Settlement->value,
                'status' => TransactionStatus::Completed->value,
                'posting_status' => PostingStatus::Posted->value,
                'reference_number' => $settlement->settlement_number,
                'transaction_reference' => $txnRef,
                'sequence_number' => $this->getNextSequenceNumber($wallet->id),
                'invoice_id' => $settlement->invoice_id,
                'settlement_id' => $settlement->id,
            ]);

            $wallet->update(['balance' => $currentBalance]);

            // Reconciled metadata references
            $eventData = [
                'wallet_id' => $wallet->id,
                'amount_cents' => $amountCents,
                'settlement_id' => $settlementId,
                'invoice_id' => $settlement->invoice_id,
                'transaction_reference' => $txnRef,
            ];

            $this->lifecycleService->recordSettlementCredited($wallet, $eventData);
        });
    }

    /**
     * Compute balance authoritative by summing transactions dynamically
     */
    public function calculateDynamicBalance(string $walletId): int
    {
        $wallet = Wallet::findOrFail($walletId);
        $accountId = $wallet->ledger_account_id;

        $credits = (int) \App\Platform\Accounting\Journal\LedgerEntry::where('ledger_account_id', $accountId)
            ->where('entry_type', \App\Platform\Accounting\Journal\EntryType::Credit->value)
            ->sum('amount_cents');

        $debits = (int) \App\Platform\Accounting\Journal\LedgerEntry::where('ledger_account_id', $accountId)
            ->where('entry_type', \App\Platform\Accounting\Journal\EntryType::Debit->value)
            ->sum('amount_cents');

        return $credits - $debits;
    }

    protected function getNextSequenceNumber(string $walletId): int
    {
        return (int) (WalletTransaction::where('wallet_id', $walletId)->max('sequence_number') ?? 0) + 1;
    }

    protected function ensurePeriodNotLocked(): void
    {
        $month = (int) date('m');
        $year = date('Y');
        $period = AccountingPeriod::where('fiscal_year', $year)
            ->where('month', $month)
            ->first();

        if ($period && $period->status === 'locked') {
            throw new \RuntimeException("Accounting period is locked.");
        }
    }
}
