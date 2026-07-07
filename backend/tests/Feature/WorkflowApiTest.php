<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Platform\Workflows\Application\Services\WorkflowEngineService;
use App\Platform\Workflows\Domain\Entities\WorkflowInstance;
use App\Platform\Workflows\Domain\Entities\WorkflowTask;
use App\Platform\Workflows\Domain\Enums\WorkflowStatus;
use App\Platform\Workflows\Domain\Enums\TaskStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class WorkflowApiTest extends ApiTestCase
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

        // Pre-create basic dependencies
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
            'booking_code' => 'BK-TEST-WF-API',
            'customer_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'subtotal_cents' => 100000,
            'tax_cents' => 18000,
            'grand_total_cents' => 118000,
            'currency' => 'INR',
            'status' => BookingStatus::Draft,
        ]);

        // Publish Workflow Definition using DSL Publisher
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
                    'sla_hours' => 24,
                    'approval_mode' => 'any',
                ],
                [
                    'name' => 'Finance Director Review',
                    'role' => 'super_admin',
                    'order' => 2,
                    'sla_hours' => 12,
                    'approval_mode' => 'all',
                ],
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
                ],
            ],
        ];

        $publisher->publish('Booking Approval Workflow', 'booking.approval', Booking::class, $dsl);
    }

    public function test_workflow_lifecycle_success_path(): void
    {
        // Pre-create super admins so they exist when step 2 activates (ALL mode)
        $admin1 = User::factory()->create();
        $admin1->assignRole('super_admin');

        $admin2 = User::factory()->create();
        $admin2->assignRole('super_admin');

        // 1. Start Workflow
        $instance = $this->engine->start('booking.approval', Booking::class, $this->booking->id, [
            'booking_code' => $this->booking->booking_code,
        ]);

        $this->assertDatabaseHas('workflow_instances', [
            'id' => $instance->id,
            'status' => WorkflowStatus::Active->value,
        ]);

        // Step 1 should create a single role-based task assigned to branch_manager
        $task = WorkflowTask::where('instance_id', $instance->id)->first();
        $this->assertNotNull($task);
        $this->assertEquals(TaskStatus::Pending, $task->status);

        // 2. Action Step 1 (Approve)
        $manager = User::factory()->create();
        $manager->assignRole('branch_manager');

        $this->actingAs($manager);

        $response = $this->postJson("/api/v1/workflows/tasks/{$task->id}/action", [
            'action' => 'approve',
            'comments' => 'Branch manager approves.',
        ]);

        $this->assertApiResponse($response, 200);

        // Booking status should be updated to branch_review intermediate status
        $this->booking->refresh();
        $this->assertEquals(BookingStatus::BranchReview, $this->booking->status);

        // Step 2 ALL mode: should have created tasks for both super admins (represented by user assignments)
        $tasks = WorkflowTask::where('instance_id', $instance->id)
            ->where('status', TaskStatus::Assigned)
            ->get();

        $this->assertCount(1, $tasks); // Generic task is created, but has user assignments for both users

        // Action task 1 (Approve)
        $this->actingAs($admin1);
        $response = $this->postJson("/api/v1/workflows/tasks/{$tasks[0]->id}/action", [
            'action' => 'approve',
            'comments' => 'Admin 1 approves.',
        ]);
        $this->assertApiResponse($response, 200);

        // Workflow should now be fully complete and booking status updated to Approved!
        $instance->refresh();
        $this->assertEquals(WorkflowStatus::Completed, $instance->status);

        $this->booking->refresh();
        $this->assertEquals(BookingStatus::Approved, $this->booking->status);
    }

    public function test_workflow_rejection_path(): void
    {
        $instance = $this->engine->start('booking.approval', Booking::class, $this->booking->id, [
            'booking_code' => $this->booking->booking_code,
        ]);

        $task = WorkflowTask::where('instance_id', $instance->id)->firstOrFail();

        $manager = User::factory()->create();
        $manager->assignRole('branch_manager');

        $this->actingAs($manager);

        // Reject workflow task
        $response = $this->postJson("/api/v1/workflows/tasks/{$task->id}/action", [
            'action' => 'reject',
            'comments' => 'Details incorrect.',
        ]);

        $this->assertApiResponse($response, 200);

        $instance->refresh();
        $this->assertEquals(WorkflowStatus::Terminated, $instance->status);

        $this->booking->refresh();
        $this->assertEquals(BookingStatus::Rejected, $this->booking->status);
    }

    public function test_workflow_sla_breach_escalation(): void
    {
        $instance = $this->engine->start('booking.approval', Booking::class, $this->booking->id, [
            'booking_code' => $this->booking->booking_code,
        ]);

        $task = WorkflowTask::where('instance_id', $instance->id)->firstOrFail();

        // Manipulate due_at to simulate overdue task
        $task->update(['due_at' => now()->subMinutes(1)]);

        // Run SLA check
        $this->engine->escalateOverdueTasks();

        $task->refresh();
        $this->assertEquals(TaskStatus::Escalated, $task->status);
    }
}
