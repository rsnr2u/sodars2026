<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderBankAccount;
use App\Modules\Providers\Domain\Entities\ProviderDocument;
use App\Modules\Providers\Domain\Entities\ProviderStaff;
use App\Modules\Providers\Domain\Entities\ProviderSubscription;
use App\Modules\Providers\Domain\Entities\ProviderActivity;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Application\Services\ProviderLifecycleService;
use App\Modules\Finance\Domain\Entities\ProviderSettlement;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Platform\Identity\Domain\Entities\Organization;
use App\Platform\Identity\Domain\Entities\OrganizationMember;
use App\Platform\Search\Application\Jobs\UpdateIndexDocumentJob;
use App\Platform\Reporting\Infrastructure\Reports\ProviderSettlementReport;
use App\Platform\Reporting\Infrastructure\Reports\ProviderPerformanceReport;
use App\Platform\Reporting\Infrastructure\Reports\ProviderActivityReport;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Core\Context\ContextManager;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\GeographySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class ProviderIntegrationTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;
    protected Organization $org1;
    protected Organization $org2;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        ContextManager::clear();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(GeographySeeder::class);

        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();

        // Tenants
        $this->org1 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Provider Org One',
            'slug' => 'prov-org-one',
            'is_active' => true,
        ]);

        $this->org2 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Provider Org Two',
            'slug' => 'prov-org-two',
            'is_active' => true,
        ]);

        // Memberships
        OrganizationMember::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->org1->id,
            'user_id' => $this->user1->id,
            'role' => 'admin',
            'status' => 'active',
            'joined_at' => now(),
        ]);
        $this->user1->update(['organization_id' => $this->org1->id]);

        OrganizationMember::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->org2->id,
            'user_id' => $this->user2->id,
            'role' => 'admin',
            'status' => 'active',
            'joined_at' => now(),
        ]);
        $this->user2->update(['organization_id' => $this->org2->id]);

        // Branch for providers
        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'HQ Branch',
            'code' => 'HQ-B',
            'support_email' => 'hq@sodars.com',
            'support_phone' => '+918000000000',
        ]);
    }

    // ─────────────────────────────────────────────────────
    // 1. Tenant Isolation (RLS)
    // ─────────────────────────────────────────────────────

    public function test_provider_tenant_isolation_rls(): void
    {
        // Org 1 creates provider
        $this->actingAs($this->user1);
        ContextManager::boot();

        $provider1 = Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Org 1 Provider LLC',
            'registration_number' => 'REG-ORG1-999',
            'provider_code' => 'PRV-ORG1-001',
            'default_branch_id' => $this->branch->id,
            'status' => ProviderStatus::Draft->value,
        ]);

        // Org 2 creates provider
        $this->actingAs($this->user2);
        ContextManager::boot();

        $provider2 = Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Org 2 Provider LLC',
            'registration_number' => 'REG-ORG2-999',
            'provider_code' => 'PRV-ORG2-001',
            'default_branch_id' => $this->branch->id,
            'status' => ProviderStatus::Draft->value,
        ]);

        // Query under Org 1 – should only see Org 1 provider
        $this->actingAs($this->user1);
        ContextManager::boot();

        $this->assertCount(1, Provider::all());
        $this->assertEquals('Org 1 Provider LLC', Provider::first()->company_name);
        $this->assertEquals($this->org1->id, Provider::first()->organization_id);

        // Query under Org 2 – should only see Org 2 provider
        $this->actingAs($this->user2);
        ContextManager::boot();

        $this->assertCount(1, Provider::all());
        $this->assertEquals('Org 2 Provider LLC', Provider::first()->company_name);
        $this->assertEquals($this->org2->id, Provider::first()->organization_id);
    }

    // ─────────────────────────────────────────────────────
    // 2. Audit Logs
    // ─────────────────────────────────────────────────────

    public function test_provider_lifecycle_audit_logging(): void
    {
        $this->actingAs($this->user1);
        ContextManager::boot();

        $provider = Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Audit Provider LLC',
            'registration_number' => 'REG-AUDIT-999',
            'provider_code' => 'PRV-AUDIT-001',
            'default_branch_id' => $this->branch->id,
            'status' => ProviderStatus::Draft->value,
        ]);

        $lifecycle = app(ProviderLifecycleService::class);

        // 1. Record creation
        $lifecycle->recordCreation($provider);

        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'provider.profile.created',
            'organization_id' => $this->org1->id,
        ]);

        // 2. Transition status (Verified)
        $lifecycle->transitionTo($provider, ProviderStatus::Pending->value);
        $lifecycle->transitionTo($provider, ProviderStatus::Verified->value);

        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'provider.profile.verified',
            'organization_id' => $this->org1->id,
        ]);
    }

    // ─────────────────────────────────────────────────────
    // 3. Search Index Reindexing
    // ─────────────────────────────────────────────────────

    public function test_provider_search_reindexing_job_dispatch(): void
    {
        Queue::fake();

        $this->actingAs($this->user1);
        ContextManager::boot();

        $provider = Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Search Provider LLC',
            'registration_number' => 'REG-SEARCH-999',
            'provider_code' => 'PRV-SEARCH-001',
            'default_branch_id' => $this->branch->id,
            'status' => ProviderStatus::Draft->value,
        ]);

        event(new \App\Modules\Providers\Domain\Events\ProviderCreated(
            aggregateId: $provider->id,
            aggregateVersion: 1,
            data: $provider->toArray(),
        ));

        // Assert job is pushed for provider index update
        Queue::assertPushed(UpdateIndexDocumentJob::class, function ($job) use ($provider) {
            return $job->entityId === $provider->id;
        });
    }

    // ─────────────────────────────────────────────────────
    // 4. Custom Reports Tenant Scoping
    // ─────────────────────────────────────────────────────

    public function test_provider_reports_tenant_scoping(): void
    {
        // Setup Provider & Settlement under Org 1
        $this->actingAs($this->user1);
        ContextManager::boot();

        $provider1 = Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Org 1 Report LLC',
            'registration_number' => 'REG-RPT-999',
            'provider_code' => 'PRV-RPT-001',
            'default_branch_id' => $this->branch->id,
            'status' => ProviderStatus::Verified->value,
        ]);

        $booking1 = Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => 'B-RPT-001',
            'customer_id' => $this->user1->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(5),
            'grand_total_cents' => 100000,
            'status' => 'approved',
        ]);

        $invoice1 = Invoice::create([
            'id' => (string) Str::uuid(),
            'invoice_number' => 'INV-RPT-001',
            'customer_id' => $this->user1->id,
            'branch_id' => $this->branch->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal_cents' => 100000,
            'discount_cents' => 0,
            'tax_cents' => 18000,
            'grand_total_cents' => 118000,
            'currency' => 'INR',
            'status' => 'issued',
            'invoice_type' => 'tax_invoice',
            'booking_snapshot' => [],
        ]);

        ProviderSettlement::create([
            'id' => (string) Str::uuid(),
            'provider_id' => $provider1->id,
            'period_start' => now()->toDateString(),
            'period_end' => now()->addDays(30)->toDateString(),
            'total_amount_cents' => 100000,
            'provider_share_cents' => 80000,
            'commission_cents' => 15000,
            'tax_cents' => 5000,
            'booking_id' => $booking1->id,
            'invoice_id' => $invoice1->id,
            'settlement_number' => 'SETTLE-001',
            'status' => 'pending',
        ]);

        // Setup Provider & Settlement under Org 2
        $this->actingAs($this->user2);
        ContextManager::boot();

        $provider2 = Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Org 2 Report LLC',
            'registration_number' => 'REG-RPT-888',
            'provider_code' => 'PRV-RPT-002',
            'default_branch_id' => $this->branch->id,
            'status' => ProviderStatus::Verified->value,
        ]);

        $booking2 = Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => 'B-RPT-002',
            'customer_id' => $this->user2->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(5),
            'grand_total_cents' => 200000,
            'status' => 'approved',
        ]);

        $invoice2 = Invoice::create([
            'id' => (string) Str::uuid(),
            'invoice_number' => 'INV-RPT-002',
            'customer_id' => $this->user2->id,
            'branch_id' => $this->branch->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal_cents' => 200000,
            'discount_cents' => 0,
            'tax_cents' => 36000,
            'grand_total_cents' => 236000,
            'currency' => 'INR',
            'status' => 'issued',
            'invoice_type' => 'tax_invoice',
            'booking_snapshot' => [],
        ]);

        ProviderSettlement::create([
            'id' => (string) Str::uuid(),
            'provider_id' => $provider2->id,
            'period_start' => now()->toDateString(),
            'period_end' => now()->addDays(30)->toDateString(),
            'total_amount_cents' => 200000,
            'provider_share_cents' => 160000,
            'commission_cents' => 30000,
            'tax_cents' => 10000,
            'booking_id' => $booking2->id,
            'invoice_id' => $invoice2->id,
            'settlement_number' => 'SETTLE-002',
            'status' => 'pending',
        ]);

        // Generate Settlement report under Org 1 context
        $this->actingAs($this->user1);
        ContextManager::boot();

        $report = new ProviderSettlementReport();
        $params = ReportParameters::fromArray([]);
        $result = $report->generate($params);

        $this->assertEquals(1, $result['summary']['count']);
        $this->assertEquals(100000, $result['summary']['total_settled_cents']);
        $this->assertCount(1, $result['records']);
        $this->assertEquals($provider1->id, $result['records'][0]['provider_id']);

        // Generate Performance report under Org 1 context
        $perfReport = new ProviderPerformanceReport();
        $perfResult = $perfReport->generate($params);

        $this->assertEquals(1, $perfResult['summary']['total_providers']);
        $this->assertCount(1, $perfResult['records']);
        $this->assertEquals('Org 1 Report LLC', $perfResult['records'][0]['company_name']);
    }

    // ─────────────────────────────────────────────────────
    // 5. Lifecycle Service Verification
    // ─────────────────────────────────────────────────────

    public function test_provider_lifecycle_service_transitions(): void
    {
        $this->actingAs($this->user1);
        ContextManager::boot();

        $provider = Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Lifecycle LLC',
            'registration_number' => 'REG-LIFECYCLE-999',
            'provider_code' => 'PRV-LIFECYCLE-001',
            'default_branch_id' => $this->branch->id,
            'status' => ProviderStatus::Draft->value,
        ]);

        $lifecycle = app(ProviderLifecycleService::class);

        // Valid transition Draft -> Pending
        $lifecycle->transitionTo($provider, ProviderStatus::Pending->value);
        $this->assertEquals(ProviderStatus::Pending, $provider->status);

        // Invalid transition Pending -> Suspended (should fail)
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $lifecycle->transitionTo($provider, ProviderStatus::Suspended->value);
    }
}
