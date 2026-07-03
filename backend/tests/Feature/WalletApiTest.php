<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Modules\Wallet\Domain\Entities\WalletTransaction;
use App\Modules\Wallet\Domain\Entities\Withdrawal;
use App\Modules\Wallet\Domain\Services\WalletBalanceCalculator;
use App\Modules\Wallet\Domain\Services\WalletService;
use App\Modules\Wallet\Domain\Enums\WithdrawalStatus;
use App\Platform\Accounting\ChartOfAccounts\LedgerAccount;
use App\Platform\Accounting\ChartOfAccounts\AccountingPeriod;
use App\Platform\Accounting\Journal\LedgerJournal;
use App\Platform\Accounting\Database\Seeders\ChartOfAccountsSeeder;
use App\Modules\Finance\Domain\Events\SettlementPaid;
use App\Modules\Finance\Domain\Entities\ProviderSettlement;
use App\Core\ValueObjects\Money;
use App\Core\ValueObjects\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class WalletApiTest extends ApiTestCase
{
    use RefreshDatabase;

    protected string $providerId;
    protected string $branchId;
    protected Wallet $providerWallet;
    protected WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->seed(ChartOfAccountsSeeder::class);
        $this->walletService = app(WalletService::class);

        // Create Branch
        $branch = \App\Modules\Branches\Domain\Entities\Branch::create([
            'name' => 'HQ Branch',
            'code' => 'HQ-B',
            'support_email' => 'hq@sodars.com',
            'support_phone' => '+91800100',
        ]);
        $this->branchId = $branch->id;

        // Create Provider
        $provider = Provider::create([
            'company_name' => 'Goliath Billboards',
            'registration_number' => 'REG-GOLIATH-101',
            'provider_code' => 'GOL-PRV-01',
            'status' => 'verified',
            'default_branch_id' => $this->branchId,
        ]);
        $this->providerId = $provider->id;

        // Setup provider wallet
        $this->providerWallet = $this->walletService->createWallet($provider, 'provider', 'INR');
    }

    public function test_dynamic_balance_computed_from_ledger(): void
    {
        $admin = $this->actingAsAdmin();

        // 1. Initial balance should be 0
        $response = $this->getJson("/api/v1/wallets/{$this->providerWallet->id}");
        $this->assertApiResponse($response, 200);
        $response->assertJsonPath('data.balance_cents', 0);

        // 2. Add Deposit
        $depositResponse = $this->postJson("/api/v1/wallets/{$this->providerWallet->id}/deposit", [
            'amount_cents' => 85000,
            'reference' => 'TXN-DEP-101',
            'metadata' => ['bank_ref' => 'SBI-100200'],
        ]);
        $this->assertApiResponse($depositResponse, 201);
        $depositResponse->assertJsonPath('data.running_balance_snapshot', 85000);

        // 3. Balance checks
        $response = $this->getJson("/api/v1/wallets/{$this->providerWallet->id}");
        $response->assertJsonPath('data.balance_cents', 85000);
    }

    public function test_withdrawal_workflows_and_validation(): void
    {
        $admin = $this->actingAsAdmin();

        // 1. Setup funds via deposit
        $this->walletService->deposit($this->providerWallet, 50000, 'DEP-WTH-PREP');

        // 2. Insufficient balance validation checks
        $failWithdraw = $this->postJson("/api/v1/wallets/{$this->providerWallet->id}/withdrawals", [
            'amount_cents' => 60000,
            'bank_account_details' => [
                'account_number' => '1234567890',
                'bank_name' => 'HDFC Bank',
                'ifsc_code' => 'HDFC0000123',
            ]
        ]);
        $failWithdraw->assertStatus(500); // throws Insufficient balance exception

        // 3. File Valid withdrawal payout
        $withdrawalResponse = $this->postJson("/api/v1/wallets/{$this->providerWallet->id}/withdrawals", [
            'amount_cents' => 20000,
            'bank_account_details' => [
                'account_number' => '1234567890',
                'bank_name' => 'HDFC Bank',
                'ifsc_code' => 'HDFC0000123',
            ]
        ]);
        $this->assertApiResponse($withdrawalResponse, 201);
        $withdrawalId = $withdrawalResponse->json('data.id');

        // 4. Complete / Process withdrawal payout
        $processResponse = $this->patchJson("/api/v1/withdrawals/{$withdrawalId}/process", [
            'status' => 'completed',
            'payout_reference' => 'BANK-PAY-999',
        ]);
        $this->assertApiResponse($processResponse, 200);

        // 5. Assert database state & balance updated to 30000 (50000 - 20000)
        $this->assertDatabaseHas('withdrawals', [
            'id' => $withdrawalId,
            'status' => WithdrawalStatus::Completed->value,
            'payout_reference' => 'BANK-PAY-999',
        ]);

        $balanceResponse = $this->getJson("/api/v1/wallets/{$this->providerWallet->id}");
        $balanceResponse->assertJsonPath('data.balance_cents', 30000);
    }

    public function test_internal_wallet_transfers(): void
    {
        $admin = $this->actingAsAdmin();

        // 1. Create recipient provider wallet
        $recipientProvider = Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Central Ad Media',
            'registration_number' => 'REG-CENTRAL-202',
            'provider_code' => 'CEN-PRV-02',
            'status' => 'verified',
            'default_branch_id' => $this->branchId,
        ]);
        $recipientWallet = $this->walletService->createWallet($recipientProvider, 'provider', 'INR');

        // 2. Deposit funds to source
        $this->walletService->deposit($this->providerWallet, 100000, 'DEP-TRF-PREP');

        // 3. Initiate Transfer API
        $transferResponse = $this->postJson("/api/v1/wallets/{$this->providerWallet->id}/transfer", [
            'to_wallet_id' => $recipientWallet->id,
            'amount_cents' => 40000,
            'reference' => 'TRF-DEMO-001',
        ]);
        $this->assertApiResponse($transferResponse, 200);

        // 4. Check balances on both sides
        $sourceBal = $this->getJson("/api/v1/wallets/{$this->providerWallet->id}");
        $sourceBal->assertJsonPath('data.balance_cents', 60000);

        $destBal = $this->getJson("/api/v1/wallets/{$recipientWallet->id}");
        $destBal->assertJsonPath('data.balance_cents', 40000);
    }

    public function test_settlement_paid_event_chain_integration(): void
    {
        $user = User::factory()->create();
        
        $booking = \App\Modules\Bookings\Domain\Entities\Booking::create([
            'booking_code' => 'BK-TEST-SETTLE',
            'customer_id' => $user->id,
            'branch_id' => $this->branchId,
            'status' => 'approved',
            'currency' => 'INR',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'total_cents' => 100000,
            'discount_cents' => 0,
            'tax_cents' => 18000,
            'grand_total_cents' => 118000,
        ]);
        $bookingId = $booking->id;

        $invoice = \App\Modules\Finance\Domain\Entities\Invoice::create([
            'invoice_number' => 'INV-TEST-SETTLE',
            'booking_id' => $bookingId,
            'customer_id' => $user->id,
            'branch_id' => $this->branchId,
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'subtotal_cents' => 100000,
            'discount_cents' => 0,
            'tax_cents' => 18000,
            'grand_total_cents' => 118000,
            'currency' => 'INR',
            'status' => 'issued',
            'invoice_type' => 'tax_invoice',
            'booking_snapshot' => [],
        ]);
        $invoiceId = $invoice->id;

        // 1. Create a dummy ProviderSettlement in database
        $settlementId = (string) Str::uuid();
        $settlement = ProviderSettlement::create([
            'id' => $settlementId,
            'provider_id' => $this->providerId,
            'settlement_number' => 'SET-100200300',
            'booking_id' => $bookingId,
            'invoice_id' => $invoiceId,
            'status' => 'paid',
            'total_amount_cents' => 100000,
            'commission_cents' => 15000,
            'tax_cents' => 5000,
            'provider_share_cents' => 80000,
            'payout_reference' => 'BANK-SETTLE-101',
        ]);

        Event::dispatch(new SettlementPaid(
            aggregateId: $settlementId,
            aggregateVersion: 1,
            data: ['net_payout_cents' => 80000],
            occurredAt: now()->toIso8601String(),
            correlationId: (string) Str::uuid(),
            traceId: (string) Str::uuid()
        ));

        // 3. Assert provider's wallet has been credited automatically
        $this->actingAsAdmin();
        $balanceResponse = $this->getJson("/api/v1/wallets/{$this->providerWallet->id}");
        $balanceResponse->assertJsonPath('data.balance_cents', 80000);
    }

    public function test_closed_accounting_periods_block_postings(): void
    {
        // 1. Find or create accounting period matching current month, and lock it
        $month = (int) date('m');
        $year = date('Y');
        $period = AccountingPeriod::where('fiscal_year', $year)
            ->where('month', $month)
            ->first();

        if (!$period) {
            $period = AccountingPeriod::create([
                'id' => (string) Str::uuid(),
                'fiscal_year' => $year,
                'month' => $month,
                'status' => 'locked',
            ]);
        } else {
            $period->update(['status' => 'locked']);
        }

        // 2. Attempt deposit should fail
        $this->actingAsAdmin();
        $response = $this->postJson("/api/v1/wallets/{$this->providerWallet->id}/deposit", [
            'amount_cents' => 5000,
            'reference' => 'FAIL-LOCKED',
        ]);

        $response->assertStatus(500); // blocks posting with locked exception
    }
}
