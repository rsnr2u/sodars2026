<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use App\Modules\Bookings\Domain\Services\BookingLifecycleService;
use App\Platform\Identity\Domain\Entities\Organization;
use App\Platform\Identity\Domain\Entities\OrganizationMember;
use App\Platform\Audit\Domain\Entities\AuditEvent;
use App\Platform\Audit\Domain\Enums\EventCategory;
use App\Platform\Audit\Domain\Enums\RiskLevel;
use App\Platform\Search\Application\Jobs\UpdateIndexDocumentJob;
use App\Platform\Reporting\Infrastructure\Reports\BookingPerformanceReport;
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

class BookingIntegrationTest extends ApiTestCase
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
    }

    // ─────────────────────────────────────────────────────
    // 1. Multi-Tenant Row Level Isolation
    // ─────────────────────────────────────────────────────

    public function test_booking_tenant_isolation_rls(): void
    {
        // Save booking under Org 1 context
        $this->actingAs($this->user1);
        ContextManager::boot();

        $booking1 = Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => 'B-ORG1-001',
            'customer_id' => $this->user1->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(5),
            'grand_total_cents' => 50000,
            'status' => BookingStatus::Draft->value,
        ]);

        // Switch to Org 2 context
        $this->actingAs($this->user2);
        ContextManager::boot();

        $booking2 = Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => 'B-ORG2-002',
            'customer_id' => $this->user2->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(5),
            'grand_total_cents' => 90000,
            'status' => BookingStatus::Draft->value,
        ]);

        // In Org 2, query bookings. We should ONLY see booking2
        $results = Booking::all();
        $this->assertCount(1, $results);
        $this->assertEquals('B-ORG2-002', $results->first()->booking_code);

        // Attempting to query booking1 from Org 2 should fail via find
        $this->assertNull(Booking::find($booking1->id));
    }

    // ─────────────────────────────────────────────────────
    // 2. Automated & Event-Driven Audit Tracing
    // ─────────────────────────────────────────────────────

    public function test_booking_workflow_lifecycle_and_audit_snapshots(): void
    {
        $this->actingAs($this->user1);
        ContextManager::boot();

        $booking = Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => 'B-LIFECYCLE-01',
            'customer_id' => $this->user1->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(4),
            'grand_total_cents' => 100000,
            'status' => BookingStatus::Draft->value,
        ]);

        // Boot Trace Context to verify correlation hierarchy
        app(TraceContext::class)->setCorrelationId($corrId = (string) Str::uuid());
        app(TraceContext::class)->setTraceId($traceId = (string) Str::uuid());

        // Perform Workflow State Transition: Draft -> Submitted
        $lifecycle = app(BookingLifecycleService::class);
        $lifecycle->transition($booking, BookingStatus::Submitted->value, 'Customer submitted booking details');
        $booking->refresh();

        // Check if event listener created detailed AuditEvent log
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'booking.submitted',
            'category' => EventCategory::Business->value,
            'organization_id' => $this->org1->id,
            'user_id' => $this->user1->id,
            'correlation_id' => $corrId,
            'trace_id' => $traceId,
        ]);

        $auditLog = AuditEvent::where('event_type', 'booking.submitted')->first();
        $this->assertEquals('draft', $auditLog->metadata['from_status']);
        $this->assertEquals('submitted', $auditLog->metadata['to_status']);
        $this->assertEquals('Customer submitted booking details', $auditLog->metadata['comment']);

        // Check Eloquent model auditable snapshots (Auditable trait log)
        // Transition: Submitted -> BranchReview (simulating payment recording in pipeline)
        $booking->update(['status' => BookingStatus::BranchReview->value]);
        $booking->refresh();

        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'model.updated',
            'auditable_type' => Booking::class,
            'auditable_id' => $booking->id,
        ]);

        $modelAudit = AuditEvent::where('event_type', 'model.updated')
            ->where('after_snapshot->status', 'branch_review')
            ->first();
        $this->assertEquals('submitted', $modelAudit->before_snapshot['status']);
        $this->assertEquals('branch_review', $modelAudit->after_snapshot['status']);
    }

    // ─────────────────────────────────────────────────────
    // 3. Search platform indexing auto-trigger
    // ─────────────────────────────────────────────────────

    public function test_booking_reindexing_job_dispatch(): void
    {
        Queue::fake();

        $this->actingAs($this->user1);
        ContextManager::boot();

        $booking = Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => 'B-INDEX-001',
            'customer_id' => $this->user1->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(5),
            'grand_total_cents' => 120000,
            'status' => BookingStatus::Draft->value,
        ]);

        // Manually dispatch BookingCreated event to simulate pipeline execution
        event(new \App\Modules\Bookings\Domain\Events\BookingCreated(
            aggregateId: $booking->id,
            aggregateVersion: 1,
            data: $booking->toArray(),
            occurredAt: now()->toIso8601String(),
            correlationId: (string) Str::uuid(),
            traceId: (string) Str::uuid()
        ));

        // Assert job was dispatched for creation
        Queue::assertPushed(UpdateIndexDocumentJob::class, function ($job) use ($booking) {
            return $job->action === 'index' && $job->entityId === $booking->id;
        });

        // Trigger transition
        $lifecycle = app(BookingLifecycleService::class);
        $lifecycle->transition($booking, BookingStatus::Submitted->value);

        // Assert job was dispatched for update
        Queue::assertPushed(UpdateIndexDocumentJob::class, function ($job) use ($booking) {
            return $job->action === 'index' && $job->entityId === $booking->id;
        });
    }

    // ─────────────────────────────────────────────────────
    // 4. Reporting performance and utilization
    // ─────────────────────────────────────────────────────

    public function test_booking_performance_report_scoping(): void
    {
        $this->actingAs($this->user1);
        ContextManager::boot();

        // Create booking under Org 1
        Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => 'B-REPORT-001',
            'customer_id' => $this->user1->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(4),
            'grand_total_cents' => 150000,
            'status' => BookingStatus::Approved->value,
        ]);

        // Create booking under Org 2
        $this->actingAs($this->user2);
        ContextManager::boot();

        Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => 'B-REPORT-002',
            'customer_id' => $this->user2->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(4),
            'grand_total_cents' => 200000,
            'status' => BookingStatus::Approved->value,
        ]);

        // Run report under Org 1 context. Should only include Org 1 performance data
        $this->actingAs($this->user1);
        ContextManager::boot();

        $report = new BookingPerformanceReport();
        $params = ReportParameters::fromArray(['status' => 'Approved']);
        $data = $report->generate($params);

        $this->assertEquals(1, $data['summary']['total_bookings']);
        $this->assertEquals(150000, $data['summary']['total_revenue_cents']);
        $this->assertCount(1, $data['records']);
        $this->assertEquals('B-REPORT-001', $data['records'][0]['booking_code']);
    }
}
