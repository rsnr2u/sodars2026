<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Platform\Workflows\Application\Services\WorkflowEngineService;
use App\Platform\Workflows\Domain\Entities\WorkflowDefinition;
use App\Platform\Workflows\Domain\Entities\WorkflowDefinitionStep;
use App\Platform\Workflows\Domain\Entities\WorkflowInstance;
use App\Platform\Workflows\Domain\Entities\WorkflowTask;
use App\Platform\Workflows\Domain\Enums\WorkflowStatus;
use App\Platform\Workflows\Domain\Enums\TaskStatus;
use App\Platform\Workflows\Domain\Enums\ApprovalMode;
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
    protected WorkflowDefinition $definition;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->engine = app(WorkflowEngineService::class);

        // Create Branch
        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'HQ Branch',
            'code' => 'HQ-B',
            'support_email' => 'hq@sodars.com',
            'support_phone' => '+91800100',
        ]);

        // Create Booking
        $customer = User::factory()->create();
        $this->booking = Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => 'BK-WORKFLOW-TEST',
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

        // Create Workflow Definition
        $this->definition = WorkflowDefinition::create([
            'id' => (string) Str::uuid(),
            'name' => 'Booking Approval Workflow',
            'key' => 'booking.approval',
            'version' => 1,
            'entity_type' => Booking::class,
            'is_active' => true,
        ]);

        // Step 1: Branch Manager (ANY approval)
        WorkflowDefinitionStep::create([
            'id' => (string) Str::uuid(),
            'definition_id' => $this->definition->id,
            'name' => 'Branch Manager Review',
            'role' => 'branch_manager',
            'order' => 1,
            'sla_hours' => 24,
            'approval_mode' => ApprovalMode::Any,
        ]);

        // Step 2: Super Admin (ALL approval)
        WorkflowDefinitionStep::create([
            'id' => (string) Str::uuid(),
            'definition_id' => $this->definition->id,
            'name' => 'Finance Director Review',
            'role' => 'super_admin',
            'order' => 2,
            'sla_hours' => 12,
            'approval_mode' => ApprovalMode::All,
        ]);
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
            'current_step_index' => 0,
        ]);

        // Step 1 should create a single role-based task assigned to branch_manager
        $task = WorkflowTask::where('instance_id', $instance->id)->first();
        $this->assertNotNull($task);
        $this->assertEquals(TaskStatus::Pending, $task->status);
        $this->assertEquals('branch_manager', $task->assigned_role);

        // 2. Action Step 1 (Approve)
        $manager = User::factory()->create();
        $manager->assignRole('branch_manager');

        $this->actingAs($manager);

        $response = $this->postJson("/api/v1/workflows/tasks/{$task->id}/action", [
            'action' => 'approve',
            'comments' => 'Branch manager approves.',
        ]);

        $this->assertApiResponse($response, 200);

        // Assert step advanced to step index 1
        $instance->refresh();
        $this->assertEquals(1, $instance->current_step_index);

        // Step 2 ALL mode: should have created tasks for both super admins
        $tasks = WorkflowTask::where('instance_id', $instance->id)
            ->where('status', TaskStatus::Assigned)
            ->get();

        $this->assertCount(2, $tasks);

        // Action task 1 (Approve)
        $this->actingAs($admin1);
        $response = $this->postJson("/api/v1/workflows/tasks/{$tasks[0]->id}/action", [
            'action' => 'approve',
            'comments' => 'Admin 1 approves.',
        ]);
        $this->assertApiResponse($response, 200);

        // Workflow should still be active (ALL mode not completed yet)
        $instance->refresh();
        $this->assertEquals(WorkflowStatus::Active, $instance->status);

        // Action task 2 (Approve)
        $this->actingAs($admin2);
        $response = $this->postJson("/api/v1/workflows/tasks/{$tasks[1]->id}/action", [
            'action' => 'approve',
            'comments' => 'Admin 2 approves.',
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
        $this->assertEquals('super_admin', $task->escalated_to_role);
    }
}
