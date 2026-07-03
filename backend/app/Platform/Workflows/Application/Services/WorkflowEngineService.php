<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Application\Services;

use App\Models\User;
use App\Platform\Workflows\Domain\Entities\WorkflowDefinition;
use App\Platform\Workflows\Domain\Entities\WorkflowDefinitionStep;
use App\Platform\Workflows\Domain\Entities\WorkflowInstance;
use App\Platform\Workflows\Domain\Entities\WorkflowTask;
use App\Platform\Workflows\Domain\Entities\WorkflowHistory;
use App\Platform\Workflows\Domain\Enums\WorkflowStatus;
use App\Platform\Workflows\Domain\Enums\TaskStatus;
use App\Platform\Workflows\Domain\Enums\ApprovalMode;
use App\Platform\Workflows\Domain\Events\WorkflowStarted;
use App\Platform\Workflows\Domain\Events\WorkflowTaskAssigned;
use App\Platform\Workflows\Domain\Events\WorkflowTaskCompleted;
use App\Platform\Workflows\Domain\Events\WorkflowCompleted;
use App\Platform\Workflows\Domain\Events\WorkflowCancelled;
use App\Platform\Workflows\Infrastructure\Registry\WorkflowRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class WorkflowEngineService
{
    public function __construct(
        protected WorkflowRegistry $registry
    ) {}

    /**
     * Start a new workflow instance for an entity.
     */
    public function start(
        string $definitionKey,
        string $entityType,
        string $entityId,
        array $context = [],
        ?string $userId = null
    ): WorkflowInstance {
        return DB::transaction(function () use ($definitionKey, $entityType, $entityId, $context, $userId) {
            $definition = WorkflowDefinition::where('key', $definitionKey)
                ->where('is_active', true)
                ->orderBy('version', 'desc')
                ->firstOrFail();

            // Create workflow instance
            $instance = WorkflowInstance::create([
                'id' => (string) Str::uuid(),
                'definition_id' => $definition->id,
                'entity_id' => $entityId,
                'entity_type' => $entityType,
                'status' => WorkflowStatus::Active,
                'current_step_index' => 0,
                'context_snapshot' => $context,
                'started_at' => now(),
            ]);

            // Create initial history log
            WorkflowHistory::create([
                'id' => (string) Str::uuid(),
                'instance_id' => $instance->id,
                'from_status' => null,
                'to_status' => 'active',
                'action' => 'start',
                'comments' => 'Workflow process started.',
                'actioned_by' => $userId,
                'created_at' => now(),
            ]);

            event(new WorkflowStarted($instance->id, $context, $userId));

            // Move to first step
            $this->activateStep($instance, 0, $userId);

            return $instance;
        });
    }

    /**
     * Action a specific workflow task (approve, reject, delegate, request_changes).
     */
    public function actionTask(
        string $taskId,
        string $action,
        string $userId,
        ?string $comments = null
    ): void {
        DB::transaction(function () use ($taskId, $action, $userId, $comments) {
            $task = WorkflowTask::findOrFail($taskId);
            $instance = $task->instance;

            if ($instance->status !== WorkflowStatus::Active) {
                throw new RuntimeException("Cannot action task. Workflow instance is not active.");
            }

            if ($task->status !== TaskStatus::Pending && $task->status !== TaskStatus::Assigned) {
                throw new RuntimeException("Task is already in status: {$task->status->value}");
            }

            $oldStatus = $instance->status->value;

            if ($action === 'approve') {
                $task->update([
                    'status' => TaskStatus::Approved,
                    'actioned_by' => $userId,
                    'actioned_at' => now(),
                    'comments' => $comments,
                ]);

                event(new WorkflowTaskCompleted($instance->id, ['task_id' => $taskId, 'action' => 'approve'], $userId));

                // Log history
                WorkflowHistory::create([
                    'id' => (string) Str::uuid(),
                    'instance_id' => $instance->id,
                    'from_status' => 'active',
                    'to_status' => 'active',
                    'action' => 'approve_step',
                    'comments' => "Step approved. Task: {$task->step->name}. " . ($comments ?? ''),
                    'actioned_by' => $userId,
                    'created_at' => now(),
                ]);

                // Check if step is fully complete
                $step = $task->step;
                $isStepComplete = false;

                if ($step->approval_mode === ApprovalMode::All) {
                    // All tasks for this step must be approved
                    $pendingCount = WorkflowTask::where('instance_id', $instance->id)
                        ->where('step_id', $step->id)
                        ->whereIn('status', [TaskStatus::Pending, TaskStatus::Assigned])
                        ->count();

                    if ($pendingCount === 0) {
                        $isStepComplete = true;
                    }
                } else {
                    // ANY approval completes the step
                    $isStepComplete = true;
                    // Cancel other pending tasks for this step
                    WorkflowTask::where('instance_id', $instance->id)
                        ->where('step_id', $step->id)
                        ->whereIn('status', [TaskStatus::Pending, TaskStatus::Assigned])
                        ->update(['status' => TaskStatus::Cancelled]);
                }

                if ($isStepComplete) {
                    $nextStepIndex = $instance->current_step_index + 1;
                    $nextStep = WorkflowDefinitionStep::where('definition_id', $instance->definition_id)
                        ->where('order', $nextStepIndex + 1) // order is 1-indexed, step index is 0-indexed
                        ->first();

                    if ($nextStep) {
                        $instance->update(['current_step_index' => $nextStepIndex]);
                        $this->activateStep($instance, $nextStepIndex, $userId);
                    } else {
                        // Workflow complete - execute approve callback
                        $instance->update([
                            'status' => WorkflowStatus::Completed,
                            'completed_at' => now(),
                        ]);

                        $handler = $this->registry->resolve($instance->entity_type);
                        $result = $handler->approve($instance);

                        WorkflowHistory::create([
                            'id' => (string) Str::uuid(),
                            'instance_id' => $instance->id,
                            'from_status' => 'active',
                            'to_status' => 'completed',
                            'action' => 'complete',
                            'comments' => "Workflow fully approved and completed. Target status: {$result->newStatus}",
                            'actioned_by' => $userId,
                            'created_at' => now(),
                        ]);

                        event(new WorkflowCompleted($instance->id, $result->metadata, $userId));
                    }
                }
            } elseif ($action === 'reject') {
                $task->update([
                    'status' => TaskStatus::Rejected,
                    'actioned_by' => $userId,
                    'actioned_at' => now(),
                    'comments' => $comments,
                ]);

                // Cancel all other tasks for this instance
                WorkflowTask::where('instance_id', $instance->id)
                    ->whereIn('status', [TaskStatus::Pending, TaskStatus::Assigned])
                    ->update(['status' => TaskStatus::Cancelled]);

                $instance->update([
                    'status' => WorkflowStatus::Terminated,
                    'completed_at' => now(),
                ]);

                $handler = $this->registry->resolve($instance->entity_type);
                $result = $handler->reject($instance);

                WorkflowHistory::create([
                    'id' => (string) Str::uuid(),
                    'instance_id' => $instance->id,
                    'from_status' => 'active',
                    'to_status' => 'terminated',
                    'action' => 'reject_step',
                    'comments' => "Workflow rejected at step: {$task->step->name}. " . ($comments ?? ''),
                    'actioned_by' => $userId,
                    'created_at' => now(),
                ]);

                event(new WorkflowCancelled($instance->id, $result->metadata, $userId));
            } elseif ($action === 'request_changes') {
                $task->update([
                    'status' => TaskStatus::Pending,
                    'comments' => $comments,
                ]);

                $handler = $this->registry->resolve($instance->entity_type);
                $result = $handler->requestChanges($instance);

                WorkflowHistory::create([
                    'id' => (string) Str::uuid(),
                    'instance_id' => $instance->id,
                    'from_status' => 'active',
                    'to_status' => 'active',
                    'action' => 'request_changes',
                    'comments' => "Changes requested: " . ($comments ?? ''),
                    'actioned_by' => $userId,
                    'created_at' => now(),
                ]);

                event(new WorkflowTaskCompleted($instance->id, ['task_id' => $taskId, 'action' => 'request_changes'], $userId));
            }
        });
    }

    /**
     * Activate tasks for a specific step index.
     */
    protected function activateStep(WorkflowInstance $instance, int $stepIndex, ?string $userId = null): void
    {
        $step = WorkflowDefinitionStep::where('definition_id', $instance->definition_id)
            ->where('order', $stepIndex + 1)
            ->firstOrFail();

        $dueAt = $step->sla_hours ? now()->addHours($step->sla_hours) : null;

        if ($step->approval_mode === ApprovalMode::All) {
            // Find all users of role
            $users = User::whereHas('roles', function ($q) use ($step) {
                $q->where('name', $step->role);
            })->get();

            if ($users->isEmpty()) {
                // Fallback to create single role-based task if no specific users assigned to role yet
                $task = WorkflowTask::create([
                    'id' => (string) Str::uuid(),
                    'instance_id' => $instance->id,
                    'step_id' => $step->id,
                    'status' => TaskStatus::Pending,
                    'assigned_role' => $step->role,
                    'due_at' => $dueAt,
                ]);

                event(new WorkflowTaskAssigned($instance->id, ['task_id' => $task->id, 'role' => $step->role], $userId));
            } else {
                foreach ($users as $user) {
                    $task = WorkflowTask::create([
                        'id' => (string) Str::uuid(),
                        'instance_id' => $instance->id,
                        'step_id' => $step->id,
                        'status' => TaskStatus::Assigned,
                        'assigned_role' => $step->role,
                        'assigned_user_id' => $user->id,
                        'due_at' => $dueAt,
                    ]);

                    event(new WorkflowTaskAssigned($instance->id, ['task_id' => $task->id, 'user_id' => $user->id], $userId));
                }
            }
        } else {
            // ANY: Create single role task
            $task = WorkflowTask::create([
                'id' => (string) Str::uuid(),
                'instance_id' => $instance->id,
                'step_id' => $step->id,
                'status' => TaskStatus::Pending,
                'assigned_role' => $step->role,
                'due_at' => $dueAt,
            ]);

            event(new WorkflowTaskAssigned($instance->id, ['task_id' => $task->id, 'role' => $step->role], $userId));
        }
    }

    /**
     * Check SLA deadlines and escalate overdue tasks.
     */
    public function escalateOverdueTasks(): void
    {
        DB::transaction(function () {
            $overdueTasks = WorkflowTask::whereIn('status', [TaskStatus::Pending, TaskStatus::Assigned])
                ->whereNotNull('due_at')
                ->where('due_at', '<=', now())
                ->get();

            foreach ($overdueTasks as $task) {
                $task->update([
                    'status' => TaskStatus::Escalated,
                    'escalated_to_role' => 'super_admin',
                    'escalated_at' => now(),
                ]);

                WorkflowHistory::create([
                    'id' => (string) Str::uuid(),
                    'instance_id' => $task->instance_id,
                    'from_status' => 'active',
                    'to_status' => 'active',
                    'action' => 'escalate',
                    'comments' => "Task SLA breached. Escalated step [{$task->step->name}] to super_admin.",
                    'actioned_by' => null,
                    'created_at' => now(),
                ]);

                event(new WorkflowTaskAssigned($task->instance_id, ['task_id' => $task->id, 'role' => 'super_admin'], null));
            }
        });
    }
}
