<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Platform\Workflows\Application\Services\WorkflowEngineService;
use App\Platform\Scheduler\Application\Services\SchedulerService;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use App\Platform\Workflows\Domain\Entities\WorkflowInstance;
use App\Platform\Workflows\Domain\Entities\WorkflowTask;
use App\Platform\Workflows\Domain\Entities\WorkflowExecutionToken;
use App\Platform\Workflows\Domain\Enums\WorkflowStatus;
use App\Platform\Workflows\Domain\Enums\TaskStatus;
use App\Platform\Workflows\Domain\Services\RuleEngine;
use App\Platform\Workflows\Domain\Services\WorkflowMetricsEngine;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class AdvancedBpmRuntimeTest extends ApiTestCase
{
    use RefreshDatabase;

    protected WorkflowEngineService $engine;
    protected Branch $branch;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->engine = app(WorkflowEngineService::class);

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'HQ Branch',
            'code' => 'HQ-B',
            'support_email' => 'hq@sodars.com',
            'support_phone' => '+91800100',
        ]);

        $customer = User::factory()->create();
        $this->booking = Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => 'BK-BPM-RUNTIME',
            'customer_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'subtotal_cents' => 1500000,
            'tax_cents' => 270000,
            'grand_total_cents' => 1770000,
            'currency' => 'INR',
            'status' => BookingStatus::Draft,
        ]);
    }

    public function test_ast_rule_engine_evaluation(): void
    {
        $engine = app(RuleEngine::class);

        $variables = [
            'budget' => 150000,
            'risk_score' => 85,
            'country' => 'IN',
        ];

        // 1. Success cases
        $this->assertTrue($engine->evaluate("budget > 100000 && risk_score >= 80", $variables));
        $this->assertTrue($engine->evaluate("country == 'IN'", $variables));

        // 2. Failure cases
        $this->assertFalse($engine->evaluate("budget > 200000", $variables));
        $this->assertFalse($engine->evaluate("country != 'IN'", $variables));
    }

    public function test_saga_rollback_compensation(): void
    {
        $publisher = app(\App\Platform\Workflows\Domain\Services\WorkflowDefinitionPublisher::class);

        $dsl = [
            'key' => 'booking.approval',
            'states' => ['draft', 'branch_review', 'approved', 'rejected'],
            'initial_state' => 'draft',
            'steps' => [
                [
                    'name' => 'Branch Manager Review',
                    'role' => 'branch_manager',
                    'order' => 1,
                    'approval_mode' => 'any',
                ],
                [
                    'name' => 'Finance Review',
                    'role' => 'super_admin',
                    'order' => 2,
                    'approval_mode' => 'any',
                ]
            ],
            'transitions' => [
                [
                    'name' => 'approve',
                    'from' => 'draft',
                    'to' => 'branch_review',
                ],
                [
                    'name' => 'approve',
                    'from' => 'branch_review',
                    'to' => 'approved',
                ],
                [
                    'name' => 'reject',
                    'from' => 'draft',
                    'to' => 'rejected',
                ],
                [
                    'name' => 'reject',
                    'from' => 'branch_review',
                    'to' => 'rejected',
                ]
            ]
        ];

        $publisher->publish('Booking Workflow', 'booking.approval', Booking::class, $dsl);

        // Start Workflow (state becomes draft)
        $instance = $this->engine->start('booking.approval', Booking::class, $this->booking->id, [
            'booking_code' => $this->booking->booking_code,
        ]);

        $task = WorkflowTask::where('instance_id', $instance->id)->firstOrFail();

        $manager = User::factory()->create();
        $manager->assignRole('branch_manager');
        $this->actingAs($manager);

        // Transition status from draft -> branch_review. Spawns next task for Finance Review.
        $this->engine->actionTask($task->id, 'approve', $manager->id, 'Approved step 1.');

        $this->booking->refresh();
        $this->assertEquals(BookingStatus::BranchReview, $this->booking->status);

        // Fetch task 2 (Finance Review)
        $task2 = WorkflowTask::where('instance_id', $instance->id)
            ->where('step_name', 'Finance Review')
            ->firstOrFail();

        // Reject step 2. This will trigger saga compensation rollbacks for completed step 1!
        $this->engine->actionTask($task2->id, 'reject', $manager->id, 'Rejection trigger.');

        // Assert booking is rolled back to draft due to Saga compensation!
        $this->booking->refresh();
        $this->assertEquals(BookingStatus::Draft, $this->booking->status);
    }

    public function test_parallel_gateway_execution_tokens(): void
    {
        $publisher = app(\App\Platform\Workflows\Domain\Services\WorkflowDefinitionPublisher::class);

        $dsl = [
            'key' => 'booking.approval',
            'states' => ['draft', 'approved'],
            'initial_state' => 'draft',
            'steps' => [
                [
                    'name' => 'Parallel Approvals',
                    'type' => 'parallel_gateway',
                    'order' => 1,
                    'branches' => [
                        [
                            'name' => 'Branch A',
                            'role' => 'branch_manager',
                        ],
                        [
                            'name' => 'Branch B',
                            'role' => 'super_admin',
                        ]
                    ]
                ]
            ],
            'transitions' => [
                [
                    'name' => 'approve',
                    'from' => 'draft',
                    'to' => 'approved',
                ]
            ]
        ];

        $publisher->publish('Parallel Booking Workflow', 'booking.approval', Booking::class, $dsl);

        $instance = $this->engine->start('booking.approval', Booking::class, $this->booking->id, [
            'booking_code' => $this->booking->booking_code,
        ]);

        // Should have created 2 active execution tokens for the parallel branches
        $this->assertEquals(2, WorkflowExecutionToken::where('workflow_instance_id', $instance->id)->where('status', 'active')->count());

        $tasks = WorkflowTask::where('instance_id', $instance->id)->get();
        $this->assertCount(2, $tasks);

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        // Action task 1 (approves Branch A branch token)
        $this->engine->actionTask($tasks[0]->id, 'approve', $admin->id, 'Approves branch 1.');

        $instance->refresh();
        // Workflow should still be Active (waiting on second branch token!)
        $this->assertEquals(WorkflowStatus::Active, $instance->status);

        // Action task 2 (approves Branch B branch token)
        $this->engine->actionTask($tasks[1]->id, 'approve', $admin->id, 'Approves branch 2.');

        $instance->refresh();
        // Now workflow should be Completed!
        $this->assertEquals(WorkflowStatus::Completed, $instance->status);
    }

    public function test_runtime_metrics_collection(): void
    {
        $metricsEngine = app(WorkflowMetricsEngine::class);

        // Setup a completed instance
        $publisher = app(\App\Platform\Workflows\Domain\Services\WorkflowDefinitionPublisher::class);
        $dsl = [
            'key' => 'booking.approval',
            'states' => ['draft', 'approved'],
            'initial_state' => 'draft',
            'steps' => [
                [
                    'name' => 'Step 1',
                    'role' => 'super_admin',
                    'order' => 1,
                ]
            ],
            'transitions' => [
                [
                    'name' => 'approve',
                    'from' => 'draft',
                    'to' => 'approved',
                ]
            ]
        ];
        $publisher->publish('Metrics Test', 'booking.approval', Booking::class, $dsl);

        $instance = $this->engine->start('booking.approval', Booking::class, $this->booking->id);

        $task = WorkflowTask::where('instance_id', $instance->id)->firstOrFail();
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        $this->engine->actionTask($task->id, 'approve', $admin->id);

        // Fetch metrics
        $metrics = $metricsEngine->getMetrics();

        $this->assertGreaterThan(0, $metrics['total_instances']);
        $this->assertEquals(1, $metrics['completed_instances']);
    }

    public function test_scheduler_jobs_execution_and_retry_strategies(): void
    {
        $scheduler = app(SchedulerService::class);

        // Schedule a job that fails to assert retry backoff calculations
        $job = $scheduler->schedule(
            category: 'workflow',
            jobType: 'timeout',
            aggregateType: 'TestAggregate',
            aggregateId: '12345',
            executeAt: now()->subMinutes(1),
            payload: ['task_id' => 'nonexistent_id'], // Will trigger model not found error
            retryPolicy: [
                'strategy' => 'exponential',
                'max_attempts' => 2,
                'initial_delay' => 60,
                'multiplier' => 2,
            ]
        );

        $this->assertEquals('pending', $job->status);

        // Execute first failure attempt (reschedules)
        $scheduler->executeJob($job);
        $job->refresh();

        $this->assertEquals('pending', $job->status);
        $this->assertEquals(1, $job->attempts);
        $this->assertGreaterThan(now()->getTimestamp(), $job->execute_at->getTimestamp());

        // Execute second failure attempt (moves to failed / DLQ)
        $job->update(['execute_at' => now()->subMinutes(1)]);
        $scheduler->executeJob($job);
        $job->refresh();

        $this->assertEquals('failed', $job->status);
        $this->assertEquals(2, $job->attempts);
        $this->assertNotNull($job->last_error);
    }
}
