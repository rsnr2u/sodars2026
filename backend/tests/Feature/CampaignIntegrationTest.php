<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignCreative;
use App\Modules\Campaigns\Domain\Entities\CampaignProof;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;
use App\Modules\Campaigns\Application\Services\CampaignLifecycleService;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Platform\Identity\Domain\Entities\Organization;
use App\Platform\Identity\Domain\Entities\OrganizationMember;
use App\Platform\Search\Application\Jobs\UpdateIndexDocumentJob;
use App\Platform\Reporting\Infrastructure\Reports\CampaignPerformanceReport;
use App\Platform\Reporting\Infrastructure\Reports\CampaignTimelineReport;
use App\Platform\Reporting\Infrastructure\Reports\CampaignUtilizationReport;
use App\Platform\Reporting\Infrastructure\Reports\CampaignActivityReport;
use App\Platform\Reporting\Infrastructure\Reports\CampaignOccupancyReport;
use App\Platform\Reporting\Infrastructure\Reports\CampaignSettlementReport;
use App\Platform\Reporting\Infrastructure\Reports\CampaignRevenueReport;
use App\Platform\Reporting\Infrastructure\Reports\CampaignBudgetVarianceReport;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Core\Context\ContextManager;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\GeographySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class CampaignIntegrationTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;
    protected Organization $org1;
    protected Organization $org2;
    protected Branch $branch;
    protected InventoryFace $face;

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
            'name' => 'Campaign Org One',
            'slug' => 'cmp-org-one',
            'is_active' => true,
        ]);

        $this->org2 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Campaign Org Two',
            'slug' => 'cmp-org-two',
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

        // Branch
        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'HQ Branch',
            'code' => 'HQ-B',
            'support_email' => 'hq@sodars.com',
            'support_phone' => '+918000000000',
        ]);

        // Provider
        $provider = \App\Modules\Providers\Domain\Entities\Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Ad Provider',
            'registration_number' => 'REG-CMP-PRV',
            'provider_code' => 'PRV-CMP-01',
            'default_branch_id' => $this->branch->id,
            'status' => 'verified',
            'preferred_payout_method' => 'bank',
        ]);

        $country = \App\Platform\Shared\Domain\Entities\Country::first();
        $state = \App\Platform\Shared\Domain\Entities\State::first();
        $district = \App\Platform\Shared\Domain\Entities\District::first();
        $city = \App\Platform\Shared\Domain\Entities\City::first();
        $pincode = \App\Platform\Shared\Domain\Entities\Pincode::first();

        // Create Face
        $inventory = Inventory::create([
            'id' => (string) Str::uuid(),
            'inventory_code' => 'INV-TEST-001',
            'display_name' => 'Billboard City',
            'provider_id' => $provider->id,
            'branch_id' => $this->branch->id,
            'country_id' => $country?->id,
            'state_id' => $state?->id,
            'district_id' => $district?->id,
            'city_id' => $city?->id,
            'pincode_id' => $pincode?->id,
            'inventory_category' => 'Static',
            'inventory_type' => 'Billboard',
            'ownership_type' => 'owned',
            'latitude' => 17.4401,
            'longitude' => 78.3489,
            'geo_hash' => 'te7u61gf',
            'normalized_address' => 'Hitec City, Hyderabad',
            'status' => 'approved',
            'ai_scores' => [
                'visibility_score' => 80,
                'traffic_score' => 80,
                'engagement_score' => 80,
                'overall_score' => 80
            ],
            'inventory_capabilities' => [
                'supportsAudio' => false,
                'supportsVideo' => false,
                'supportsInteractive' => false,
                'supportsProgrammatic' => false,
                'hasLighting' => true,
                'hasCamera' => false,
                'hasWifi' => false,
                'maxResolutionWidth' => null,
                'maxResolutionHeight' => null
            ]
        ]);

        $this->face = InventoryFace::create([
            'id' => (string) Str::uuid(),
            'inventory_id' => $inventory->id,
            'face_code' => 'INV-TEST-001-F1',
            'display_name' => 'Front Side',
            'facing_direction' => 'north',
            'display_order' => 1,
            'is_active' => true,
            'physical_specifications' => [
                'width_cm' => 1200,
                'height_cm' => 600,
                'orientation' => 'landscape',
                'illuminated' => true
            ]
        ]);
    }

    // ─────────────────────────────────────────────────────
    // 1. Tenant Isolation (RLS)
    // ─────────────────────────────────────────────────────

    public function test_campaign_tenant_isolation_rls(): void
    {
        // Org 1 creates campaign
        $this->actingAs($this->user1);
        ContextManager::boot();

        $campaign1 = Campaign::create([
            'id' => (string) Str::uuid(),
            'name' => 'Org 1 Promo Launch',
            'campaign_code' => 'CMP-ORG1-001',
            'customer_id' => $this->user1->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'status' => CampaignStatus::Draft->value,
        ]);

        // Org 2 creates campaign
        $this->actingAs($this->user2);
        ContextManager::boot();

        $campaign2 = Campaign::create([
            'id' => (string) Str::uuid(),
            'name' => 'Org 2 Promo Launch',
            'campaign_code' => 'CMP-ORG2-001',
            'customer_id' => $this->user2->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'status' => CampaignStatus::Draft->value,
        ]);

        // Query under Org 1 – should only see Org 1 campaign
        $this->actingAs($this->user1);
        ContextManager::boot();

        $this->assertCount(1, Campaign::all());
        $this->assertEquals('Org 1 Promo Launch', Campaign::first()->name);
        $this->assertEquals($this->org1->id, Campaign::first()->organization_id);

        // Query under Org 2 – should only see Org 2 campaign
        $this->actingAs($this->user2);
        ContextManager::boot();

        $this->assertCount(1, Campaign::all());
        $this->assertEquals('Org 2 Promo Launch', Campaign::first()->name);
        $this->assertEquals($this->org2->id, Campaign::first()->organization_id);
    }

    // ─────────────────────────────────────────────────────
    // 2. Audit Logs
    // ─────────────────────────────────────────────────────

    public function test_campaign_lifecycle_audit_logging(): void
    {
        $this->actingAs($this->user1);
        ContextManager::boot();

        $campaign = Campaign::create([
            'id' => (string) Str::uuid(),
            'name' => 'Audit Campaign',
            'campaign_code' => 'CMP-AUDIT-001',
            'customer_id' => $this->user1->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'status' => CampaignStatus::Draft->value,
        ]);

        $lifecycle = app(CampaignLifecycleService::class);

        // 1. Record creation
        $lifecycle->recordCreation($campaign);

        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'campaign.profile.created',
            'organization_id' => $this->org1->id,
        ]);

        // 2. Transition status (Planning)
        $lifecycle->transitionTo($campaign, CampaignStatus::Planning->value);

        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'campaign.profile.updated',
            'organization_id' => $this->org1->id,
        ]);
    }

    // ─────────────────────────────────────────────────────
    // 3. Search Index Reindexing
    // ─────────────────────────────────────────────────────

    public function test_campaign_search_reindexing_job_dispatch(): void
    {
        Queue::fake();

        $this->actingAs($this->user1);
        ContextManager::boot();

        $campaign = Campaign::create([
            'id' => (string) Str::uuid(),
            'name' => 'Search Campaign',
            'campaign_code' => 'CMP-SEARCH-001',
            'customer_id' => $this->user1->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'status' => CampaignStatus::Draft->value,
        ]);

        event(new \App\Modules\Campaigns\Domain\Events\CampaignCreated(
            aggregateId: $campaign->id,
            aggregateVersion: 1,
            data: $campaign->toArray(),
        ));

        // Assert job is pushed for campaign index update
        Queue::assertPushed(UpdateIndexDocumentJob::class, function ($job) use ($campaign) {
            return $job->entityId === $campaign->id;
        });
    }

    // ─────────────────────────────────────────────────────
    // 4. Custom Reports Tenant Scoping
    // ─────────────────────────────────────────────────────

    public function test_campaign_reports_tenant_scoping(): void
    {
        // Setup Campaign under Org 1
        $this->actingAs($this->user1);
        ContextManager::boot();

        $campaign1 = Campaign::create([
            'id' => (string) Str::uuid(),
            'name' => 'Org 1 Report Camp',
            'campaign_code' => 'CMP-RPT-001',
            'customer_id' => $this->user1->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'status' => CampaignStatus::Approved->value,
            'planned_budget_cents' => 100000,
            'approved_budget_cents' => 100000,
            'actual_spend_cents' => 80000,
            'remaining_budget_cents' => 20000,
        ]);

        // Setup Campaign under Org 2
        $this->actingAs($this->user2);
        ContextManager::boot();

        $campaign2 = Campaign::create([
            'id' => (string) Str::uuid(),
            'name' => 'Org 2 Report Camp',
            'campaign_code' => 'CMP-RPT-002',
            'customer_id' => $this->user2->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'status' => CampaignStatus::Approved->value,
            'planned_budget_cents' => 200000,
            'approved_budget_cents' => 200000,
            'actual_spend_cents' => 160000,
            'remaining_budget_cents' => 40000,
        ]);

        // Generate Performance report under Org 1 context
        $this->actingAs($this->user1);
        ContextManager::boot();

        $report = new CampaignPerformanceReport();
        $params = ReportParameters::fromArray([]);
        $result = $report->generate($params);

        $this->assertEquals(1, $result['summary']['total_campaigns']);
        $this->assertCount(1, $result['records']);
        $this->assertEquals('Org 1 Report Camp', $result['records'][0]['name']);

        // Generate Budget Variance report under Org 1 context
        $varianceReport = new CampaignBudgetVarianceReport();
        $varResult = $varianceReport->generate($params);

        $this->assertEquals(100000, $varResult['summary']['total_approved_budget_cents']);
        $this->assertEquals(80000, $varResult['summary']['total_actual_spend_cents']);
        $this->assertCount(1, $varResult['records']);
        $this->assertEquals('Org 1 Report Camp', $varResult['records'][0]['name']);
    }

    // ─────────────────────────────────────────────────────
    // 5. Lifecycle Service Verification
    // ─────────────────────────────────────────────────────

    public function test_campaign_lifecycle_service_transitions(): void
    {
        $this->actingAs($this->user1);
        ContextManager::boot();

        $campaign = Campaign::create([
            'id' => (string) Str::uuid(),
            'name' => 'Lifecycle Camp',
            'campaign_code' => 'CMP-LIFE-001',
            'customer_id' => $this->user1->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'status' => CampaignStatus::Draft->value,
        ]);

        $lifecycle = app(CampaignLifecycleService::class);

        // Valid transition Draft -> Planning
        $lifecycle->transitionTo($campaign, CampaignStatus::Planning->value);
        $this->assertEquals(CampaignStatus::Planning, $campaign->status);

        // Invalid transition Planning -> Running (should fail)
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $lifecycle->transitionTo($campaign, CampaignStatus::Running->value);
    }
}
