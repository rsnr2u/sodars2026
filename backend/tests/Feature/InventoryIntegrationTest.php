<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Inventory\Domain\Entities\InventoryAvailability;
use App\Modules\Inventory\Domain\Enums\InventoryStatus;
use App\Modules\Inventory\Domain\Enums\OwnershipType;
use App\Modules\Inventory\Application\Actions\ChangeInventoryStatusAction;
use App\Platform\Identity\Domain\Entities\Organization;
use App\Platform\Identity\Domain\Entities\OrganizationMember;
use App\Platform\Audit\Domain\Entities\AuditEvent;
use App\Platform\Audit\Domain\Enums\EventCategory;
use App\Platform\Audit\Domain\Enums\RiskLevel;
use App\Platform\Search\Application\Jobs\UpdateIndexDocumentJob;
use App\Platform\Reporting\Infrastructure\Reports\InventoryOccupancyReport;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Core\Context\ContextManager;
use App\Core\Context\TraceContext;
use Database\Seeders\GeographySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class InventoryIntegrationTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;
    protected Organization $org1;
    protected Organization $org2;
    protected Branch $branch;
    protected string $countryId;
    protected string $stateId;
    protected string $districtId;
    protected string $cityId;
    protected string $pincodeId;
    protected string $providerId;

    protected function setUp(): void
    {
        parent::setUp();
        ContextManager::clear();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(GeographySeeder::class);

        // Branch
        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'HQ Branch',
            'code' => 'HQ-B',
            'support_email' => 'hq@sodars.com',
            'support_phone' => '+918000000000',
        ]);

        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();

        // Tenants
        $this->org1 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Org One',
            'slug' => 'org-one',
            'is_active' => true,
        ]);

        $this->org2 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Org Two',
            'slug' => 'org-two',
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
        \Illuminate\Support\Facades\DB::table('branch_users')->insert([
            'id' => (string) Str::uuid(),
            'branch_id' => $this->branch->id,
            'user_id' => $this->user1->id,
            'is_primary' => true,
            'is_active' => true,
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        OrganizationMember::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->org2->id,
            'user_id' => $this->user2->id,
            'role' => 'admin',
            'status' => 'active',
            'joined_at' => now(),
        ]);
        $this->user2->update(['organization_id' => $this->org2->id]);
        \Illuminate\Support\Facades\DB::table('branch_users')->insert([
            'id' => (string) Str::uuid(),
            'branch_id' => $this->branch->id,
            'user_id' => $this->user2->id,
            'is_primary' => true,
            'is_active' => true,
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->countryId = (string) \App\Platform\Shared\Domain\Entities\Country::first()->id;
        $this->stateId = (string) \App\Platform\Shared\Domain\Entities\State::first()->id;
        $this->districtId = (string) \App\Platform\Shared\Domain\Entities\District::first()->id;
        $this->cityId = (string) \App\Platform\Shared\Domain\Entities\City::first()->id;
        $this->pincodeId = (string) \App\Platform\Shared\Domain\Entities\Pincode::first()->id;

        $provider = \App\Modules\Providers\Domain\Entities\Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Test Company',
            'registration_number' => 'REG-' . Str::random(10),
            'provider_code' => 'PROV-' . Str::random(5),
            'default_branch_id' => $this->branch->id,
            'status' => 'verified',
        ]);
        $this->providerId = $provider->id;
    }

    // ─────────────────────────────────────────────────────
    // 1. Multi-Tenant Row Level Isolation
    // ─────────────────────────────────────────────────────

    public function test_inventory_tenant_isolation_rls(): void
    {
        // Save inventory under Org 1 context
        $this->actingAs($this->user1);
        ContextManager::boot();

        $inventory1 = Inventory::create([
            'id' => (string) Str::uuid(),
            'inventory_code' => 'I-ORG1-001',
            'display_name' => 'Metro Billboard 1',
            'inventory_category' => 'billboard',
            'inventory_type' => 'traditional',
            'provider_id' => $this->providerId,
            'branch_id' => $this->branch->id,
            'country_id' => $this->countryId,
            'state_id' => $this->stateId,
            'district_id' => $this->districtId,
            'city_id' => $this->cityId,
            'pincode_id' => $this->pincodeId,
            'ownership_type' => OwnershipType::Owned->value,
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'geo_hash' => 'tdr123',
            'ai_scores' => [],
            'inventory_capabilities' => [],
            'normalized_address' => '123 Test Street, Bangalore, India',
            'status' => InventoryStatus::Draft->value,
        ]);

        // Switch to Org 2 context
        $this->actingAs($this->user2);
        ContextManager::boot();

        $inventory2 = Inventory::create([
            'id' => (string) Str::uuid(),
            'inventory_code' => 'I-ORG2-002',
            'display_name' => 'Metro Billboard 2',
            'inventory_category' => 'billboard',
            'inventory_type' => 'traditional',
            'provider_id' => $this->providerId,
            'branch_id' => $this->branch->id,
            'country_id' => $this->countryId,
            'state_id' => $this->stateId,
            'district_id' => $this->districtId,
            'city_id' => $this->cityId,
            'pincode_id' => $this->pincodeId,
            'ownership_type' => OwnershipType::Owned->value,
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'geo_hash' => 'tdr123',
            'ai_scores' => [],
            'inventory_capabilities' => [],
            'normalized_address' => '123 Test Street, Bangalore, India',
            'status' => InventoryStatus::Draft->value,
        ]);

        // In Org 2, query inventories. We should ONLY see inventory2
        $results = Inventory::all();
        $this->assertCount(1, $results);
        $this->assertEquals('I-ORG2-002', $results->first()->inventory_code);

        // Attempting to query inventory1 from Org 2 should fail via find
        $this->assertNull(Inventory::find($inventory1->id));
    }

    // ─────────────────────────────────────────────────────
    // 2. Automated & Event-Driven Audit Tracing
    // ─────────────────────────────────────────────────────

    public function test_inventory_workflow_lifecycle_and_audit_snapshots(): void
    {
        $this->actingAs($this->user1);
        ContextManager::boot();

        $inventory = Inventory::create([
            'id' => (string) Str::uuid(),
            'inventory_code' => 'I-LIFECYCLE-01',
            'display_name' => 'City HQ Billboard',
            'inventory_category' => 'billboard',
            'inventory_type' => 'traditional',
            'provider_id' => $this->providerId,
            'branch_id' => $this->branch->id,
            'country_id' => $this->countryId,
            'state_id' => $this->stateId,
            'district_id' => $this->districtId,
            'city_id' => $this->cityId,
            'pincode_id' => $this->pincodeId,
            'ownership_type' => OwnershipType::Owned->value,
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'geo_hash' => 'tdr123',
            'ai_scores' => [],
            'inventory_capabilities' => [],
            'normalized_address' => '123 Test Street, Bangalore, India',
            'status' => InventoryStatus::Draft->value,
        ]);

        // Boot Trace Context to verify correlation hierarchy
        app(TraceContext::class)->setCorrelationId($corrId = (string) Str::uuid());
        app(TraceContext::class)->setTraceId($traceId = (string) Str::uuid());

        // Perform Workflow State Transition: Draft -> PendingApproval
        $action = app(ChangeInventoryStatusAction::class);
        $action->execute($inventory->id, InventoryStatus::PendingApproval->value);
        $inventory->refresh();

        // Check if event listener created detailed AuditEvent log
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'inventory.pending_approval',
            'category' => EventCategory::Business->value,
            'organization_id' => $this->org1->id,
            'user_id' => $this->user1->id,
            'correlation_id' => $corrId,
            'trace_id' => $traceId,
        ]);

        $auditLog = AuditEvent::where('event_type', 'inventory.pending_approval')->first();
        $this->assertEquals('draft', $auditLog->metadata['from_status']);
        $this->assertEquals('pending_approval', $auditLog->metadata['to_status']);

        // Check Eloquent model auditable snapshots (Auditable trait log)
        // Transition: PendingApproval -> Approved
        $action->execute($inventory->id, InventoryStatus::Approved->value);
        $inventory->refresh();

        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'model.updated',
            'auditable_type' => Inventory::class,
            'auditable_id' => $inventory->id,
        ]);

        $modelAudit = AuditEvent::where('event_type', 'model.updated')
            ->where('after_snapshot->status', 'approved')
            ->first();
        $this->assertEquals('pending_approval', $modelAudit->before_snapshot['status']);
        $this->assertEquals('approved', $modelAudit->after_snapshot['status']);
    }

    // ─────────────────────────────────────────────────────
    // 3. Search platform indexing auto-trigger
    // ─────────────────────────────────────────────────────

    public function test_inventory_reindexing_job_dispatch(): void
    {
        Queue::fake();

        $this->actingAs($this->user1);
        ContextManager::boot();

        $inventory = Inventory::create([
            'id' => (string) Str::uuid(),
            'inventory_code' => 'I-INDEX-001',
            'display_name' => 'Search Billboard',
            'inventory_category' => 'billboard',
            'inventory_type' => 'traditional',
            'provider_id' => $this->providerId,
            'branch_id' => $this->branch->id,
            'country_id' => $this->countryId,
            'state_id' => $this->stateId,
            'district_id' => $this->districtId,
            'city_id' => $this->cityId,
            'pincode_id' => $this->pincodeId,
            'ownership_type' => OwnershipType::Owned->value,
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'geo_hash' => 'tdr123',
            'ai_scores' => [],
            'inventory_capabilities' => [],
            'normalized_address' => '123 Test Street, Bangalore, India',
            'status' => InventoryStatus::Draft->value,
        ]);

        // Manually dispatch InventoryCreated event to simulate pipeline execution
        event(new \App\Modules\Inventory\Domain\Events\InventoryCreated(
            aggregateId: $inventory->id,
            aggregateVersion: 1,
            data: $inventory->toArray(),
            occurredAt: now()->toIso8601String(),
            correlationId: (string) Str::uuid(),
            traceId: (string) Str::uuid()
        ));

        // Assert job was dispatched for creation
        Queue::assertPushed(UpdateIndexDocumentJob::class, function ($job) use ($inventory) {
            return $job->action === 'index' && $job->entityId === $inventory->id;
        });

        // Trigger transition
        $action = app(ChangeInventoryStatusAction::class);
        $action->execute($inventory->id, InventoryStatus::PendingApproval->value);

        // Assert job was dispatched for update
        Queue::assertPushed(UpdateIndexDocumentJob::class, function ($job) use ($inventory) {
            return $job->action === 'index' && $job->entityId === $inventory->id;
        });
    }

    // ─────────────────────────────────────────────────────
    // 4. Reporting occupancy scoping
    // ─────────────────────────────────────────────────────

    public function test_inventory_occupancy_report_scoping(): void
    {
        $this->actingAs($this->user1);
        ContextManager::boot();

        // Create inventory under Org 1
        $inv1 = Inventory::create([
            'id' => (string) Str::uuid(),
            'inventory_code' => 'I-REPORT-001',
            'display_name' => 'Report Billboard 1',
            'inventory_category' => 'billboard',
            'inventory_type' => 'traditional',
            'provider_id' => $this->providerId,
            'branch_id' => $this->branch->id,
            'country_id' => $this->countryId,
            'state_id' => $this->stateId,
            'district_id' => $this->districtId,
            'city_id' => $this->cityId,
            'pincode_id' => $this->pincodeId,
            'ownership_type' => OwnershipType::Owned->value,
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'geo_hash' => 'tdr123',
            'ai_scores' => [],
            'inventory_capabilities' => [],
            'normalized_address' => '123 Test Street, Bangalore, India',
            'status' => InventoryStatus::Approved->value,
        ]);

        $face1 = InventoryFace::create([
            'id' => (string) Str::uuid(),
            'inventory_id' => $inv1->id,
            'display_name' => 'Front Face',
            'face_code' => 'FF-1',
            'facing_direction' => 'north',
            'physical_specifications' => [],
            'is_active' => true,
        ]);

        InventoryAvailability::create([
            'id' => (string) Str::uuid(),
            'inventory_face_id' => $face1->id,
            'start_at' => now(),
            'end_at' => now()->addDays(5),
            'availability_status' => 'blocked',
            'reason' => 'Booking confirmed',
            'source' => 'Booking',
        ]);

        // Create inventory under Org 2
        $this->actingAs($this->user2);
        ContextManager::boot();

        $inv2 = Inventory::create([
            'id' => (string) Str::uuid(),
            'inventory_code' => 'I-REPORT-002',
            'display_name' => 'Report Billboard 2',
            'inventory_category' => 'billboard',
            'inventory_type' => 'traditional',
            'provider_id' => $this->providerId,
            'branch_id' => $this->branch->id,
            'country_id' => $this->countryId,
            'state_id' => $this->stateId,
            'district_id' => $this->districtId,
            'city_id' => $this->cityId,
            'pincode_id' => $this->pincodeId,
            'ownership_type' => OwnershipType::Owned->value,
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'geo_hash' => 'tdr123',
            'ai_scores' => [],
            'inventory_capabilities' => [],
            'normalized_address' => '123 Test Street, Bangalore, India',
            'status' => InventoryStatus::Approved->value,
        ]);

        $face2 = InventoryFace::create([
            'id' => (string) Str::uuid(),
            'inventory_id' => $inv2->id,
            'display_name' => 'Back Face',
            'face_code' => 'BF-2',
            'facing_direction' => 'north',
            'physical_specifications' => [],
            'is_active' => true,
        ]);

        InventoryAvailability::create([
            'id' => (string) Str::uuid(),
            'inventory_face_id' => $face2->id,
            'start_at' => now(),
            'end_at' => now()->addDays(5),
            'availability_status' => 'blocked',
            'reason' => 'Booking confirmed',
            'source' => 'Booking',
        ]);

        // Run report under Org 1 context. Should only include Org 1 occupancy
        $this->actingAs($this->user1);
        ContextManager::boot();

        $report = new InventoryOccupancyReport();
        $params = ReportParameters::fromArray(['status' => 'blocked']);
        $data = $report->generate($params);

        $this->assertEquals(1, $data['summary']['total_slots']);
        $this->assertEquals(1, $data['summary']['occupied_slots']);
        $this->assertEquals(100.0, $data['summary']['occupancy_rate_percentage']);
        $this->assertCount(1, $data['records']);
        $this->assertEquals('I-REPORT-001', $data['records'][0]['inventory_code']);
    }
}
