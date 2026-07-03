<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Services;

use App\Core\ValueObjects\Money;
use App\Core\ValueObjects\Currency;
use App\Platform\Accounting\ChartOfAccounts\LedgerAccount;
use App\Platform\Accounting\ChartOfAccounts\AccountType;
use App\Platform\Accounting\Posting\PostingEngine;
use App\Platform\Accounting\Posting\JournalData;
use App\Platform\Accounting\Posting\LedgerLineData;
use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Modules\Wallet\Domain\Entities\WalletTransaction;
use App\Modules\Wallet\Domain\Entities\Withdrawal;
use App\Modules\Wallet\Domain\Entities\WalletActivity;
use App\Modules\Wallet\Domain\Enums\TransactionType;
use App\Modules\Wallet\Domain\Enums\TransactionStatus;
use App\Modules\Wallet\Domain\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WalletService
{
    public function __construct(
        protected PostingEngine $postingEngine,
        protected WalletBalanceCalculator $balanceCalculator
    ) {}

    /**
     * Create a new polymorphic wallet with linked general ledger account.
     */
    public function createWallet(Model $holder, string $walletType, string $currency = 'INR'): Wallet
    {
        return DB::transaction(function () use ($holder, $walletType, $currency) {
            // Find liability parent account
            $parent = LedgerAccount::where('code', '2000-LIABILITIES')->first();

            $accountCode = '2110-WALLET-' . strtoupper(Str::random(8));
            
            // Create a dedicated general ledger account
            $ledgerAccount = LedgerAccount::create([
                'id' => (string) Str::uuid(),
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

            $wallet = Wallet::create([
                'id' => (string) Str::uuid(),
                'holder_type' => $holder->getMorphClass(),
                'holder_id' => $holder->id,
                'ledger_account_id' => $ledgerAccount->id,
                'wallet_type' => $walletType,
                'currency' => $currency,
                'status' => 'active',
            ]);

            WalletActivity::create([
                'id' => (string) Str::uuid(),
                'wallet_id' => $wallet->id,
                'performed_by' => $holder->id,
                'action' => 'Created',
                'description' => "Wallet created with account {$accountCode}",
            ]);

            return $wallet;
        });
    }

    /**
     * Record dynamic deposit transaction.
     */
    public function deposit(Wallet $wallet, int $amountCents, string $reference, array $metadata = []): WalletTransaction
    {
        return DB::transaction(function () use ($wallet, $amountCents, $reference, $metadata) {
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
                    entryType: 'debit', // Asset normal balance DR increases
                    money: $money,
                    description: "Cash received for Deposit: {$reference}",
                    ledgerableType: Wallet::class,
                    ledgerableId: $wallet->id
                ),
                new LedgerLineData(
                    accountId: $walletAccount->id,
                    entryType: 'credit', // Liability normal balance CR increases
                    money: $money,
                    description: "Wallet credited for Deposit: {$reference}",
                    ledgerableType: Wallet::class,
                    ledgerableId: $wallet->id
                )
            ];

            $journal = $this->postingEngine->post($journalData, $lines);

            $currentBalance = $this->balanceCalculator->calculate($wallet);

            $tx = WalletTransaction::create([
                'id' => (string) Str::uuid(),
                'wallet_id' => $wallet->id,
                'ledger_journal_id' => $journal->id,
                'amount_cents' => $amountCents,
                'running_balance_snapshot' => $currentBalance,
                'type' => TransactionType::Deposit->value,
                'status' => TransactionStatus::Completed->value,
                'reference_number' => $reference,
                'metadata' => $metadata,
            ]);

            WalletActivity::create([
                'id' => (string) Str::uuid(),
                'wallet_id' => $wallet->id,
                'performed_by' => $wallet->holder_id,
                'action' => 'Deposit',
                'description' => "Deposit of {$money->getAmount()} cents verified.",
            ]);

            return $tx;
        });
    }

    /**
     * Request a withdrawal from the wallet.
     */
    public function requestWithdrawal(Wallet $wallet, int $amountCents, array $bankDetails): Withdrawal
    {
        return DB::transaction(function () use ($wallet, $amountCents, $bankDetails) {
            $balance = $this->balanceCalculator->calculate($wallet);

            if ($balance < $amountCents) {
                throw new InvalidArgumentException("Insufficient wallet balance.");
            }

            $withdrawal = Withdrawal::create([
                'id' => (string) Str::uuid(),
                'wallet_id' => $wallet->id,
                'amount_cents' => $amountCents,
                'bank_account_details' => $bankDetails,
                'status' => WithdrawalStatus::Requested->value,
            ]);

            WalletActivity::create([
                'id' => (string) Str::uuid(),
                'wallet_id' => $wallet->id,
                'performed_by' => $wallet->holder_id,
                'action' => 'WithdrawalRequested',
                'description' => "Requested payout withdrawal of {$amountCents} cents.",
            ]);

            return $withdrawal;
        });
    }

    /**
     * Process / Approve and Complete a withdrawal request.
     */
    public function completeWithdrawal(Withdrawal $withdrawal, string $payoutReference): void
    {
        DB::transaction(function () use ($withdrawal, $payoutReference) {
            $withdrawal->refresh();

            if ($withdrawal->status !== WithdrawalStatus::Requested) {
                throw new InvalidArgumentException("Withdrawal is not in requested status.");
            }

            $wallet = $withdrawal->wallet;
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
                    entryType: 'debit', // Debit reduces liability wallet balance
                    money: $money,
                    description: "Wallet payout debit.",
                    ledgerableType: Withdrawal::class,
                    ledgerableId: $withdrawal->id
                ),
                new LedgerLineData(
                    accountId: $bankAccount->id,
                    entryType: 'credit', // Credit reduces asset bank balance
                    money: $money,
                    description: "Bank payout credit reference: {$payoutReference}",
                    ledgerableType: Withdrawal::class,
                    ledgerableId: $withdrawal->id
                )
            ];

            $journal = $this->postingEngine->post($journalData, $lines);

            $withdrawal->update([
                'status' => WithdrawalStatus::Completed->value,
                'payout_reference' => $payoutReference,
            ]);

            $currentBalance = $this->balanceCalculator->calculate($wallet);

            WalletTransaction::create([
                'id' => (string) Str::uuid(),
                'wallet_id' => $wallet->id,
                'ledger_journal_id' => $journal->id,
                'amount_cents' => $withdrawal->amount_cents,
                'running_balance_snapshot' => $currentBalance,
                'type' => TransactionType::Withdrawal->value,
                'status' => TransactionStatus::Completed->value,
                'reference_number' => $payoutReference,
            ]);

            WalletActivity::create([
                'id' => (string) Str::uuid(),
                'wallet_id' => $wallet->id,
                'performed_by' => $wallet->holder_id,
                'action' => 'WithdrawalCompleted',
                'description' => "Withdrawal of {$withdrawal->amount_cents} cents paid successfully.",
            ]);
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

            WalletActivity::create([
                'id' => (string) Str::uuid(),
                'wallet_id' => $withdrawal->wallet_id,
                'performed_by' => $withdrawal->wallet->holder_id,
                'action' => 'WithdrawalRejected',
                'description' => "Withdrawal request rejected. Reason: {$reason}",
            ]);
        });
    }

    /**
     * Transfer funds from one wallet to another.
     */
    public function transfer(Wallet $fromWallet, Wallet $toWallet, int $amountCents, string $reference): void
    {
        DB::transaction(function () use ($fromWallet, $toWallet, $amountCents, $reference) {
            if ($fromWallet->currency !== $toWallet->currency) {
                throw new InvalidArgumentException("Currency mismatch across wallets.");
            }

            $fromBalance = $this->balanceCalculator->calculate($fromWallet);

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
                    entryType: 'debit', // Debiting sender reduces sender liability
                    money: $money,
                    description: "Transfer debit payout to {$toWallet->id}",
                    ledgerableType: Wallet::class,
                    ledgerableId: $fromWallet->id
                ),
                new LedgerLineData(
                    accountId: $toWallet->ledgerAccount->id,
                    entryType: 'credit', // Crediting receiver increases receiver liability
                    money: $money,
                    description: "Transfer credit deposit from {$fromWallet->id}",
                    ledgerableType: Wallet::class,
                    ledgerableId: $toWallet->id
                )
            ];

            $journal = $this->postingEngine->post($journalData, $lines);

            // Save snapshots for sender and receiver
            $fromBalanceSnap = $this->balanceCalculator->calculate($fromWallet);
            $toBalanceSnap = $this->balanceCalculator->calculate($toWallet);

            WalletTransaction::create([
                'id' => (string) Str::uuid(),
                'wallet_id' => $fromWallet->id,
                'ledger_journal_id' => $journal->id,
                'amount_cents' => $amountCents,
                'running_balance_snapshot' => $fromBalanceSnap,
                'type' => TransactionType::Transfer->value,
                'status' => TransactionStatus::Completed->value,
                'reference_number' => $reference,
            ]);

            WalletTransaction::create([
                'id' => (string) Str::uuid(),
                'wallet_id' => $toWallet->id,
                'ledger_journal_id' => $journal->id,
                'amount_cents' => $amountCents,
                'running_balance_snapshot' => $toBalanceSnap,
                'type' => TransactionType::Transfer->value,
                'status' => TransactionStatus::Completed->value,
                'reference_number' => $reference,
            ]);
        });
    }

    /**
     * Credit settlement from Settlement Paid event.
     */
    public function creditSettlement(Wallet $wallet, int $amountCents, string $settlementId): void
    {
        DB::transaction(function () use ($wallet, $amountCents, $settlementId) {
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
                    entryType: 'debit', // Debiting Settlement Payable reduces liability
                    money: $money,
                    description: "Payout settlement debit from payables.",
                    ledgerableType: Wallet::class,
                    ledgerableId: $wallet->id
                ),
                new LedgerLineData(
                    accountId: $walletAccount->id,
                    entryType: 'credit', // Crediting wallet liability increases partner balance
                    money: $money,
                    description: "Settlement payout credited to wallet.",
                    ledgerableType: Wallet::class,
                    ledgerableId: $wallet->id
                )
            ];

            $journal = $this->postingEngine->post($journalData, $lines);

            $currentBalance = $this->balanceCalculator->calculate($wallet);

            WalletTransaction::create([
                'id' => (string) Str::uuid(),
                'wallet_id' => $wallet->id,
                'ledger_journal_id' => $journal->id,
                'amount_cents' => $amountCents,
                'running_balance_snapshot' => $currentBalance,
                'type' => TransactionType::Settlement->value,
                'status' => TransactionStatus::Completed->value,
                'reference_number' => $settlementId,
            ]);

            WalletActivity::create([
                'id' => (string) Str::uuid(),
                'wallet_id' => $wallet->id,
                'performed_by' => $wallet->holder_id,
                'action' => 'SettlementCredited',
                'description' => "Settlement {$settlementId} auto-credited.",
            ]);
        });
    }
}
