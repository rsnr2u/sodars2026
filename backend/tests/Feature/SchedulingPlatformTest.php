<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Platform\Identity\Domain\Entities\Organization;
use App\Modules\Operations\Domain\Entities\Schedule;
use App\Modules\Operations\Domain\Entities\OperationalResource;
use App\Modules\Operations\Domain\Entities\BusinessCalendar;
use App\Modules\Operations\Domain\Entities\Shift;
use App\Modules\Operations\Domain\Entities\ScheduleAssignment;
use App\Modules\Operations\Domain\Entities\ScheduleConflict;
use App\Modules\Operations\Domain\Entities\ScheduleCheckpoint;
use App\Modules\Operations\Domain\Entities\ResourceAvailabilityProjection;
use App\Modules\Operations\Domain\Entities\ResourceWorkloadProjection;
use App\Modules\Operations\Domain\Entities\DispatchProgressProjection;
use App\Modules\Operations\Domain\Enums\ScheduleStatus;
use App\Modules\Operations\Domain\Enums\ResourceState;
use App\Modules\Operations\Domain\Services\OperationsLifecycleService;
use App\Modules\Operations\Domain\Services\OperationsMetricsEngine;
use App\Modules\Operations\Domain\Services\ConflictDetectionEngine;
use App\Modules\Operations\Domain\Services\OptimizationEngine;
use App\Modules\Operations\Domain\Services\ETAEngine;
use App\Modules\Operations\Domain\ValueObjects\RecurrencePattern;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use App\Platform\Reporting\Infrastructure\Registry\ReportingRegistry;
use App\Platform\Integrations\Infrastructure\Registry\WebhookEventRegistry;
use App\Platform\Search\Domain\Entities\SearchIndex;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;
use Carbon\Carbon;

class SchedulingPlatformTest extends ApiTestCase
{
    use RefreshDatabase;

    protected OperationsLifecycleService $service;
    protected string $orgId;
    protected OperationalResource $resource;
    protected Shift $shift;
    protected BusinessCalendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(OperationsLifecycleService::class);

        // Create organization
        $org = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Operations Corp',
            'slug' => 'ops',
            'subdomain' => 'ops',
            'status' => 'active',
        ]);
        $this->orgId = $org->id;

        // Register test resource wrapper
        $this->resource = app(\App\Modules\Operations\Domain\Managers\ResourceLifecycleManager::class)->create([
            'organization_id' => $this->orgId,
            'resource_type' => 'driver',
            'external_id' => (string) Str::uuid(),
            'display_name' => 'John Doe',
            'skills' => ['driving', 'delivery'],
            'availability_metadata' => ['working_hours' => '9-5'],
        ]);

        // Create Roster shift
        $this->shift = app(\App\Modules\Operations\Domain\Managers\ShiftLifecycleManager::class)->create([
            'organization_id' => $this->orgId,
            'name' => 'Day Shift',
            'shift_pattern' => ['start' => '09:00', 'end' => '17:00'],
        ]);

        // Create Calendar holiday mappings
        $this->calendar = app(\App\Modules\Operations\Domain\Managers\CalendarLifecycleManager::class)->create([
            'organization_id' => $this->orgId,
            'name' => 'General Operations Calendar',
            'type' => 'organization',
            'timezone' => 'UTC',
        ]);

        // Manually seed search index records for testing since RefreshDatabase wipes them
        SearchIndex::firstOrCreate(
            ['name' => 'operations_schedules'],
            [
                'id' => (string) Str::uuid(),
                'entity_type' => \App\Modules\Operations\Domain\Entities\Schedule::class,
                'field_mappings' => \App\Modules\Operations\Domain\Entities\Schedule::getSearchFieldMappings(),
                'facet_fields' => \App\Modules\Operations\Domain\Entities\Schedule::getSearchFacetFields(),
            ]
        );
    }

    public function test_schedule_lifecycle_transitions(): void
    {
        // 1. Create schedule draft
        $schedule = $this->service->createSchedule([
            'organization_id' => $this->orgId,
            'calendar_id' => $this->calendar->id,
            'shift_id' => $this->shift->id,
            'name' => 'Billboard Delivery Route',
            'schedule_type' => 'route_dispatch',
            'start_time' => now()->addHours(2)->toDateTimeString(),
            'end_time' => now()->addHours(6)->toDateTimeString(),
        ]);

        $this->assertNotNull($schedule->id);
        $this->assertEquals(ScheduleStatus::Draft, $schedule->status);

        // Verify execution tracker child record exists
        $this->assertDatabaseHas('operations_schedule_executions', [
            'schedule_id' => $schedule->id,
            'execution_status' => 'draft',
        ]);

        // 2. Transition state machine
        $this->service->transitionSchedule($schedule, ScheduleStatus::Planned);
        $this->assertEquals(ScheduleStatus::Planned, $schedule->status);

        $this->service->transitionSchedule($schedule, ScheduleStatus::Approved);
        $this->assertEquals(ScheduleStatus::Approved, $schedule->status);

        // Verify schedule snapshot captured
        $this->assertDatabaseHas('operations_schedule_snapshots', [
            'schedule_id' => $schedule->id,
            'trigger_state' => 'Approved',
        ]);
    }

    public function test_resource_assignment_and_conflict_scanning(): void
    {
        $schedule1 = $this->service->createSchedule([
            'organization_id' => $this->orgId,
            'name' => 'Morning Route 1',
            'schedule_type' => 'route_dispatch',
            'start_time' => Carbon::now()->addHours(1)->toDateTimeString(),
            'end_time' => Carbon::now()->addHours(3)->toDateTimeString(),
        ]);

        $schedule2 = $this->service->createSchedule([
            'organization_id' => $this->orgId,
            'name' => 'Overlap Morning Route 2',
            'schedule_type' => 'route_dispatch',
            'start_time' => Carbon::now()->addHours(2)->toDateTimeString(),
            'end_time' => Carbon::now()->addHours(4)->toDateTimeString(),
        ]);

        // Assign resource to Schedule 1
        $this->service->assignResource($schedule1, $this->resource);
        $this->assertDatabaseHas('operations_schedule_assignments', [
            'schedule_id' => $schedule1->id,
            'resource_id' => $this->resource->id,
        ]);

        // Rebuild availability projections (triggered by event listener)
        event(new \App\Modules\Operations\Domain\Events\ResourceAssigned($schedule1->id, 1, $schedule1->toArray()));

        // Assign same resource to Schedule 2 (causing overlap conflict)
        $this->service->assignResource($schedule2, $this->resource);

        // Run scanner directly
        $conflicts = app(ConflictDetectionEngine::class)->scanConflicts($schedule2);

        $this->assertNotEmpty($conflicts);
        $this->assertEquals('double_booking', $conflicts[0]->conflict_type->value);
    }

    public function test_recurrence_engine_evaluation(): void
    {
        $schedule = $this->service->createSchedule([
            'organization_id' => $this->orgId,
            'name' => 'Weekly Inspection',
            'schedule_type' => 'maintenance_visit',
            'start_time' => Carbon::now()->toDateTimeString(),
            'end_time' => Carbon::now()->addHours(2)->toDateTimeString(),
        ]);

        $pattern = new RecurrencePattern(
            'daily',
            2, // every 2 days
            [],
            [],
            Carbon::now()->addDays(5)->toDateTimeString() // end rule limit
        );

        $recurrences = $this->service->generateScheduleRecurrences($schedule, $pattern);

        // 5 days / 2 day interval = ~2 recurring occurrences
        $this->assertNotEmpty($recurrences);
        $this->assertDatabaseHas('operations_recurrence_rules', [
            'schedule_id' => $schedule->id,
            'frequency' => 'daily',
        ]);
    }

    public function test_gps_telemetry_eta_calculations(): void
    {
        $schedule = $this->service->createSchedule([
            'organization_id' => $this->orgId,
            'name' => 'Live Route Dispatch',
            'schedule_type' => 'route_dispatch',
            'start_time' => now()->toDateTimeString(),
            'end_time' => now()->addHours(4)->toDateTimeString(),
        ]);

        // Create sequence checkpoints
        ScheduleCheckpoint::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->orgId,
            'schedule_id' => $schedule->id,
            'name' => 'HQ Depot Warehouse',
            'sequence' => 1,
            'status' => 'pending',
            'latitude' => 28.6139,
            'longitude' => 77.2090,
        ]);

        // Simulate dispatch start
        $this->service->transitionSchedule($schedule, ScheduleStatus::InProgress);

        // Trigger telemetry location log (e.g. 5km away, traveling at 60 km/h)
        $this->service->recordTelemetryUpdate(
            $schedule,
            28.6500, // current lat
            77.2500, // current lon
            60.0 // speed
        );

        $execution = $schedule->execution()->first();
        $this->assertNotNull($execution->current_eta);
    }

    public function test_optimization_candidates_matching(): void
    {
        $resources = [$this->resource];
        $skills = ['driving'];
        $start = Carbon::now()->addHours(1);
        $end = Carbon::now()->addHours(3);

        $result = app(OptimizationEngine::class)->optimize($resources, $skills, $start, $end);

        $this->assertGreaterThan(0.0, $result->score);
    }

    public function test_reports_registration(): void
    {
        $registry = app(ReportingRegistry::class);
        $report = $registry->resolveReport('operations_schedule');

        $this->assertInstanceOf(\App\Platform\Reporting\Domain\Contracts\Report::class, $report);
    }

    public function test_webhook_event_registry_topics(): void
    {
        $this->assertTrue(WebhookEventRegistry::isValid('operations.schedule.created'));
        $this->assertTrue(WebhookEventRegistry::isValid('operations.schedule.dispatched'));
    }

    public function test_search_index_configurations(): void
    {
        $this->assertDatabaseHas('search_indexes', [
            'name' => 'operations_schedules',
        ]);
    }
}
