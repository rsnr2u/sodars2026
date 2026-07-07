<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Modules\Wallet\Domain\Entities\WalletTransaction;
use App\Modules\Wallet\Domain\Entities\Withdrawal;
use App\Modules\Wallet\Domain\Services\WalletService;
use App\Modules\Wallet\Domain\Enums\WithdrawalStatus;
use App\Modules\Wallet\Domain\Enums\WalletState;
use App\Platform\Accounting\ChartOfAccounts\LedgerAccount;
use App\Platform\Accounting\ChartOfAccounts\AccountingPeriod;
use App\Platform\Accounting\Journal\LedgerJournal;
use App\Platform\Accounting\Database\Seeders\ChartOfAccountsSeeder;
use App\Modules\Finance\Domain\Events\SettlementPaid;
use App\Modules\Finance\Domain\Entities\ProviderSettlement;
use App\Platform\Identity\Domain\Entities\Organization;
use App\Platform\Identity\Application\Services\IdentityContext;
use App\Platform\Search\Application\Jobs\RebuildIndexJob;
use App\Platform\Search\Application\Services\SearchService;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Reporting\Infrastructure\Reports\WalletBalancesReport;
use App\Platform\Reporting\Infrastructure\Reports\WalletReconciliationReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class WalletIntegrationTest extends ApiTestCase
{
    use RefreshDatabase;

    protected WalletService $walletService;
    protected string $tenantAOrgId;
    protected string $tenantBOrgId;
    protected Provider $providerA;
    protected Provider $providerB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->walletService = app(WalletService::class);

        // Define two organizations (tenants)
        $orgA = Organization::create([
            'name' => 'Tenant A Organization',
            'slug' => 'tenant-a',
            'domain' => 'tenant-a.com',
            'is_active' => true,
        ]);
        $this->tenantAOrgId = $orgA->id;

        $orgB = Organization::create([
            'name' => 'Tenant B Organization',
            'slug' => 'tenant-b',
            'domain' => 'tenant-b.com',
            'is_active' => true,
        ]);
        $this->tenantBOrgId = $orgB->id;

        $admin = $this->actingAsAdmin();

        // Seed Chart of Accounts for Tenant A
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);
        $this->seed(ChartOfAccountsSeeder::class);

        // Seed Chart of Accounts for Tenant B
        IdentityContext::setContext($admin->id, $this->tenantBOrgId, null);
        $this->seed(ChartOfAccountsSeeder::class);

        // Reset context
        IdentityContext::clear();

        // Create branches for organizations
        $branchA = \App\Modules\Branches\Domain\Entities\Branch::create([
            'name' => 'Tenant A HQ',
            'code' => 'TA-HQ',
            'support_email' => 'tahq@sodars.com',
            'support_phone' => '+91800111',
        ]);

        $branchB = \App\Modules\Branches\Domain\Entities\Branch::create([
            'name' => 'Tenant B HQ',
            'code' => 'TB-HQ',
            'support_email' => 'tbhq@sodars.com',
            'support_phone' => '+91800222',
        ]);

        // Create Providers
        $this->providerA = Provider::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->tenantAOrgId,
            'company_name' => 'Tenant A Billing Co',
            'registration_number' => 'REG-TA-101',
            'provider_code' => 'TA-PRV',
            'status' => 'verified',
            'default_branch_id' => $branchA->id,
        ]);

        $this->providerB = Provider::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->tenantBOrgId,
            'company_name' => 'Tenant B Billing Co',
            'registration_number' => 'REG-TB-202',
            'provider_code' => 'TB-PRV',
            'status' => 'verified',
            'default_branch_id' => $branchB->id,
        ]);
    }

    public function test_row_level_security_isolates_wallets_and_transactions(): void
    {
        $admin = $this->actingAsAdmin();

        // Set Identity Context to Tenant A
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);
        $walletA = $this->walletService->createWallet($this->providerA, 'provider', 'INR');
        $this->walletService->deposit($walletA, 10000, 'DEP-A');

        // Set Identity Context to Tenant B
        IdentityContext::setContext($admin->id, $this->tenantBOrgId, null);
        $walletB = $this->walletService->createWallet($this->providerB, 'provider', 'INR');
        $this->walletService->deposit($walletB, 20000, 'DEP-B');

        // Switch back to Tenant A context
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);

        // Fetch wallet lists under Tenant A context
        $tenantAWallets = Wallet::all();
        $this->assertTrue($tenantAWallets->contains('id', $walletA->id));
        $this->assertFalse($tenantAWallets->contains('id', $walletB->id));

        $tenantATransactions = WalletTransaction::all();
        $this->assertCount(1, $tenantATransactions);
        $this->assertEquals($walletA->id, $tenantATransactions->first()->wallet_id);

        // Switch to Tenant B context
        IdentityContext::setContext($admin->id, $this->tenantBOrgId, null);

        $tenantBWallets = Wallet::all();
        $this->assertFalse($tenantBWallets->contains('id', $walletA->id));
        $this->assertTrue($tenantBWallets->contains('id', $walletB->id));

        $tenantBTransactions = WalletTransaction::all();
        $this->assertCount(1, $tenantBTransactions);
        $this->assertEquals($walletB->id, $tenantBTransactions->first()->wallet_id);
    }

    public function test_transaction_immutability(): void
    {
        $admin = $this->actingAsAdmin();
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);

        $wallet = $this->walletService->createWallet($this->providerA, 'provider', 'INR');
        $tx = $this->walletService->deposit($wallet, 5000, 'DEP-TEST');

        // Attempting to update transaction must throw DomainException
        $this->expectException(\DomainException::class);
        $tx->update(['amount_cents' => 9999]);
    }

    public function test_period_locking_blocks_postings(): void
    {
        $admin = $this->actingAsAdmin();
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);

        $wallet = $this->walletService->createWallet($this->providerA, 'provider', 'INR');

        // Lock period matching current month/year
        $month = (int) date('m');
        $year = date('Y');
        AccountingPeriod::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->tenantAOrgId,
            'fiscal_year' => $year,
            'month' => $month,
            'status' => 'locked',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Accounting period is locked.');

        $this->walletService->deposit($wallet, 1000, 'DEP-FAIL');
    }

    public function test_duplicate_settlement_idempotency(): void
    {
        $admin = $this->actingAsAdmin();
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);

        $user = User::factory()->create();
        $branch = \App\Modules\Branches\Domain\Entities\Branch::first();

        $booking = \App\Modules\Bookings\Domain\Entities\Booking::create([
            'booking_code' => 'BK-TEST-IDEMP',
            'customer_id' => $user->id,
            'branch_id' => $branch->id,
            'status' => 'approved',
            'currency' => 'INR',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'total_cents' => 50000,
            'discount_cents' => 0,
            'tax_cents' => 9000,
            'grand_total_cents' => 59000,
        ]);

        $invoice = \App\Modules\Finance\Domain\Entities\Invoice::create([
            'invoice_number' => 'INV-TEST-IDEMP',
            'booking_id' => $booking->id,
            'customer_id' => $user->id,
            'branch_id' => $branch->id,
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'subtotal_cents' => 50000,
            'discount_cents' => 0,
            'tax_cents' => 9000,
            'grand_total_cents' => 59000,
            'currency' => 'INR',
            'status' => 'issued',
            'invoice_type' => 'tax_invoice',
            'booking_snapshot' => [],
        ]);

        // Create settlement
        $settlementId = (string) Str::uuid();
        $settlement = ProviderSettlement::create([
            'id' => $settlementId,
            'organization_id' => $this->tenantAOrgId,
            'provider_id' => $this->providerA->id,
            'settlement_number' => 'SET-100200',
            'booking_id' => $booking->id,
            'invoice_id' => $invoice->id,
            'status' => 'paid',
            'total_amount_cents' => 50000,
            'commission_cents' => 5000,
            'tax_cents' => 1000,
            'provider_share_cents' => 44000,
            'payout_reference' => 'PAY-111',
        ]);

        // Dispatch settlement paid event
        Event::dispatch(new SettlementPaid(
            aggregateId: $settlementId,
            aggregateVersion: 1,
            data: ['net_payout_cents' => 44000],
            occurredAt: now()->toIso8601String(),
            correlationId: (string) Str::uuid(),
            traceId: (string) Str::uuid()
        ));

        $wallet = Wallet::where('holder_id', $this->providerA->id)->firstOrFail();
        $this->assertEquals(44000, $this->walletService->calculateDynamicBalance($wallet->id));

        // Re-dispatch same settlement paid event to verify idempotency ignores it
        Event::dispatch(new SettlementPaid(
            aggregateId: $settlementId,
            aggregateVersion: 1,
            data: ['net_payout_cents' => 44000],
            occurredAt: now()->toIso8601String(),
            correlationId: (string) Str::uuid(),
            traceId: (string) Str::uuid()
        ));

        // Balance should remain exactly 44000 cents (not doubled to 88000)
        $this->assertEquals(44000, $this->walletService->calculateDynamicBalance($wallet->id));
    }

    public function test_withdrawal_race_condition(): void
    {
        $admin = $this->actingAsAdmin();
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);

        $wallet = $this->walletService->createWallet($this->providerA, 'provider', 'INR');
        $this->walletService->deposit($wallet, 10000, 'DEP-PREP');

        // Request withdrawal of the full amount
        $withdrawal1 = $this->walletService->requestWithdrawal($wallet, 10000, [
            'account_number' => '123456',
            'bank_name' => 'Test Bank',
        ]);
        $this->assertEquals(WithdrawalStatus::Requested, $withdrawal1->status);

        // Complete the first withdrawal
        $this->walletService->completeWithdrawal($withdrawal1, 'PAY-COMPLETE-1');

        // Requesting another withdrawal of 5000 must fail because authoritative balance is now 0 (10000 - 10000)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient wallet balance.');

        $this->walletService->requestWithdrawal($wallet, 5000, [
            'account_number' => '123456',
            'bank_name' => 'Test Bank',
        ]);
    }

    public function test_ledger_consistency_for_each_transaction(): void
    {
        $admin = $this->actingAsAdmin();
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);

        $wallet = $this->walletService->createWallet($this->providerA, 'provider', 'INR');
        $tx = $this->walletService->deposit($wallet, 15000, 'DEP-GL-TEST');

        // Verify that a LedgerJournal was created and linked to this WalletTransaction
        $this->assertNotNull($tx->ledger_journal_id);
        $journal = LedgerJournal::find($tx->ledger_journal_id);
        $this->assertNotNull($journal);
        $this->assertEquals('wallet', $journal->journal_type);
    }

    public function test_search_index_mapping_registration(): void
    {
        $admin = $this->actingAsAdmin();
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);

        $wallet = $this->walletService->createWallet($this->providerA, 'provider', 'INR');

        // Trigger indexing job to see if it targets the fallback 'wallet_wallets' index
        RebuildIndexJob::dispatchSync('wallet_wallets');

        $searchService = app(SearchService::class);
        $results = $searchService->search('wallet_wallets', \App\Platform\Search\Domain\ValueObjects\SearchQuery::create($wallet->wallet_number));

        $this->assertNotEmpty($results->hits);
        $this->assertEquals($wallet->wallet_number, $results->hits[0]->displayData['wallet_number']);
    }

    public function test_reports_are_tenant_scoped(): void
    {
        $admin = $this->actingAsAdmin();

        // Setup Tenant A wallet data
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);
        $walletA = $this->walletService->createWallet($this->providerA, 'provider', 'INR');
        $this->walletService->deposit($walletA, 10000, 'DEP-A');

        // Setup Tenant B wallet data
        IdentityContext::setContext($admin->id, $this->tenantBOrgId, null);
        $walletB = $this->walletService->createWallet($this->providerB, 'provider', 'INR');
        $this->walletService->deposit($walletB, 25000, 'DEP-B');

        // Run report under Tenant A context
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);

        $balancesReport = app(WalletBalancesReport::class);
        $dataA = $balancesReport->generate(new ReportParameters([]));

        $this->assertEquals(1, $dataA['summary']['total_wallets']);
        $this->assertEquals(10000, $dataA['summary']['total_cached_balance_cents']);
        $this->assertEquals($walletA->wallet_number, $dataA['records'][0]['wallet_number']);

        // Run reconciliation report under Tenant A context
        $reconReport = app(WalletReconciliationReport::class);
        $reconDataA = $reconReport->generate(new ReportParameters([]));
        $this->assertEquals(1, $reconDataA['summary']['total_reconciled']);
        $this->assertEquals(10000, $reconDataA['records'][0]['transaction_sum_cents']);
    }
}
