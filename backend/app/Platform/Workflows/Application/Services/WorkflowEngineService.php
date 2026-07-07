<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Application\Services;

use App\Models\User;
use App\Platform\Workflows\Domain\Entities\WorkflowDefinitionVersion;
use App\Platform\Workflows\Domain\Entities\WorkflowInstance;
use App\Platform\Workflows\Domain\Entities\WorkflowTask;
use App\Platform\Workflows\Domain\Entities\WorkflowTaskAssignment;
use App\Platform\Workflows\Domain\Entities\WorkflowHistory;
use App\Platform\Workflows\Domain\Entities\WorkflowVariable;
use App\Platform\Workflows\Domain\Entities\WorkflowExecutionToken;
use App\Platform\Workflows\Domain\Enums\WorkflowStatus;
use App\Platform\Workflows\Domain\Enums\TaskStatus;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowContext;
use App\Platform\Workflows\Domain\Events\WorkflowStarted;
use App\Platform\Workflows\Domain\Events\WorkflowTaskAssigned;
use App\Platform\Workflows\Domain\Events\WorkflowTransitioned;
use App\Platform\Workflows\Domain\Events\WorkflowCompleted;
use App\Platform\Workflows\Domain\Services\RuleEngine;
use App\Platform\Workflows\Infrastructure\Registry\WorkflowRegistry;
use Illuminate\Support\Facades\DB;
use App\Platform\Scheduler\Application\Services\SchedulerService;
use Illuminate\Support\Str;
use RuntimeException;

class WorkflowEngineService
{
    public function __construct(
        protected WorkflowRegistry $registry,
        protected RuleEngine $ruleEngine,
        protected SchedulerService $scheduler
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
            $version = WorkflowDefinitionVersion::whereHas('definition', function ($q) use ($definitionKey) {
                $q->where('key', $definitionKey);
            })
            ->where('is_active', true)
            ->firstOrFail();

            $dsl = $version->dsl_schema;
            $initialState = $dsl['initial_state'] ?? 'Draft';

            // Create workflow instance
            $instance = WorkflowInstance::create([
                'id' => (string) Str::uuid(),
                'definition_version_id' => $version->id,
                'entity_id' => $entityId,
                'entity_type' => $entityType,
                'organization_id' => $context['organization_id'] ?? null,
                'status' => WorkflowStatus::Active,
                'current_state' => $initialState,
                'dsl_snapshot' => $dsl,
                'context_snapshot' => $context,
                'started_at' => now(),
            ]);

            // Save variables
            foreach ($context as $key => $val) {
                if (is_scalar($val)) {
                    $type = gettype($val);
                    WorkflowVariable::create([
                        'id' => (string) Str::uuid(),
                        'instance_id' => $instance->id,
                        'name' => $key,
                        'value' => (string) $val,
                        'type' => $type,
                    ]);
                }
            }

            // Create initial history log
            WorkflowHistory::create([
                'id' => (string) Str::uuid(),
                'instance_id' => $instance->id,
                'from_state' => null,
                'to_state' => $initialState,
                'action' => 'start',
                'comments' => 'Workflow process started.',
                'actioned_by' => $userId,
                'created_at' => now(),
            ]);

            event(new WorkflowStarted($instance->id, $context, $userId));

            // Move to first step (index 0)
            $this->activateStep($instance, 0, $userId);

            return $instance;
        });
    }

    /**
     * Action a specific workflow task (approve, reject, request_changes).
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

            // 1. Cancel any active SLA timeouts scheduled for this task
            $this->scheduler->cancel(WorkflowTask::class, $task->id);

            // 2. Update task status
            $newStatus = match ($action) {
                'approve' => TaskStatus::Approved,
                'reject' => TaskStatus::Rejected,
                'request_changes' => TaskStatus::Pending, // Resets to pending for changes
                default => TaskStatus::Approved,
            };

            $task->update([
                'status' => $newStatus,
                'completed_at' => now(),
            ]);

            // Save history log
            WorkflowHistory::create([
                'id' => (string) Str::uuid(),
                'instance_id' => $instance->id,
                'task_id' => $task->id,
                'from_state' => $instance->current_state,
                'to_state' => $instance->current_state,
                'action' => $action . '_step',
                'comments' => "Step actioned. Task: {$task->step_name}. " . ($comments ?? ''),
                'actioned_by' => $userId,
                'created_at' => now(),
            ]);

            // Check parallel gateway execution tokens
            $token = WorkflowExecutionToken::where('workflow_instance_id', $instance->id)
                ->where('gateway_id', $task->step_name)
                ->where('status', 'active')
                ->first();

            if ($token) {
                $token->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }

            // 3. Evaluate if current step is fully complete
            $dsl = $instance->dsl_snapshot;
            $currentStepIndex = $instance->current_step_index ?? 0;
            $steps = $dsl['steps'] ?? [];
            $step = $steps[$currentStepIndex] ?? null;

            if (!$step) {
                throw new RuntimeException("Workflow step configuration missing for index: {$currentStepIndex}");
            }

            // Check if there are active execution tokens left for this gateway
            $activeTokensCount = WorkflowExecutionToken::where('workflow_instance_id', $instance->id)
                ->where('status', 'active')
                ->count();

            $isStepComplete = false;

            if ($action === 'reject') {
                $isStepComplete = true; // Rejecting automatically terminates
            } elseif ($activeTokensCount > 0) {
                // Parallel join is still waiting for other branches
                $isStepComplete = false;
            } else {
                $approvalMode = $step['approval_mode'] ?? 'any';

                if ($approvalMode === 'all') {
                    // Check if any other pending tasks for this step exist
                    $pendingCount = WorkflowTask::where('instance_id', $instance->id)
                        ->where('step_name', $step['name'])
                        ->whereIn('status', [TaskStatus::Pending, TaskStatus::Assigned])
                        ->count();

                    if ($pendingCount === 0) {
                        $isStepComplete = true;
                    }
                } else {
                    // ANY mode: one approval completes the step
                    $isStepComplete = true;
                    // Cancel other pending tasks for this step
                    WorkflowTask::where('instance_id', $instance->id)
                        ->where('step_name', $step['name'])
                        ->whereIn('status', [TaskStatus::Pending, TaskStatus::Assigned])
                        ->update(['status' => TaskStatus::Cancelled]);
                }
            }

            if ($isStepComplete) {
                // Check Guard Expressions
                $variables = $instance->variables()->get()->pluck('value', 'name')->toArray();
                if (isset($step['guard']['expression'])) {
                    $expr = $step['guard']['expression'];
                    if (!$this->ruleEngine->evaluate($expr, $variables)) {
                        throw new RuntimeException("Workflow transition blocked by expression guard: {$expr}");
                    }
                }

                // 4. Resolve target state transition
                $transitions = $dsl['transitions'] ?? [];
                $transitionConfig = null;

                foreach ($transitions as $t) {
                    if ($t['name'] === $action && $t['from'] === $instance->current_state) {
                        $transitionConfig = $t;
                        break;
                    }
                }

                if (!$transitionConfig) {
                    // Fallback default mapping
                    $targetState = match ($action) {
                        'approve' => 'Approved',
                        'reject' => 'Rejected',
                        'request_changes' => 'Draft',
                        default => $instance->current_state,
                    };
                } else {
                    $targetState = $transitionConfig['to'];
                }

                // 5. Delegate to the domain transition handler
                $handler = $this->registry->resolve($instance->entity_type);
                $entity = $instance->entity_type::findOrFail($instance->entity_id);

                $context = new WorkflowContext(
                    actorId: $userId,
                    organizationId: $instance->organization_id,
                    comments: $comments,
                    metadata: array_merge($instance->context_snapshot ?? [], [
                        'target_state' => $targetState
                    ])
                );

                if ($action === 'reject') {
                    // Execute the reject transition on the aggregate
                    $handler->transition($entity, $action, $context);

                    // 6. Saga compensation rollback triggered on rejection (only roll back approved tasks)
                    $histories = $instance->histories()
                        ->whereNotNull('task_id')
                        ->where('action', 'like', 'approve%')
                        ->orderBy('created_at', 'desc')
                        ->get();

                    foreach ($histories as $h) {
                        $handler->compensate($entity, $h, $context);
                    }

                    $previousState = $instance->current_state;
                    $instance->update([
                        'current_state' => $targetState,
                        'status' => WorkflowStatus::Terminated,
                        'completed_at' => now(),
                    ]);

                    WorkflowHistory::create([
                        'id' => (string) Str::uuid(),
                        'instance_id' => $instance->id,
                        'from_state' => $previousState,
                        'to_state' => $targetState,
                        'action' => 'terminate',
                        'comments' => "Workflow rejected and compensated at step: {$step['name']}.",
                        'actioned_by' => $userId,
                        'created_at' => now(),
                    ]);

                    event(new WorkflowCompleted($instance->id, [], $userId));
                } else {
                    // Approve success path
                    $result = $handler->transition($entity, $action, $context);

                    if (!$result->success) {
                        throw new RuntimeException("Domain transition handler failed to execute workflow action: {$action}");
                    }

                    $previousState = $instance->current_state;
                    $instance->update(['current_state' => $targetState]);

                    event(new WorkflowTransitioned($instance->id, $previousState, $targetState, $userId));

                    // Check next step
                    $nextStepIndex = $currentStepIndex + 1;
                    $nextStep = $steps[$nextStepIndex] ?? null;

                    if ($nextStep) {
                        $instance->update(['current_step_index' => $nextStepIndex]);
                        $this->activateStep($instance, $nextStepIndex, $userId);
                    } else {
                        // All steps completed successfully
                        $instance->update([
                            'status' => WorkflowStatus::Completed,
                            'completed_at' => now(),
                        ]);

                        WorkflowHistory::create([
                            'id' => (string) Str::uuid(),
                            'instance_id' => $instance->id,
                            'from_state' => $previousState,
                            'to_state' => $targetState,
                            'action' => 'complete',
                            'comments' => "Workflow fully completed. Target state reached: {$targetState}.",
                            'actioned_by' => $userId,
                            'created_at' => now(),
                        ]);

                        event(new WorkflowCompleted($instance->id, $result->metadata, $userId));
                    }
                }
            }
        });
    }

    /**
     * Activate tasks for a specific step index.
     */
    protected function activateStep(WorkflowInstance $instance, int $stepIndex, ?string $userId = null): void
    {
        $dsl = $instance->dsl_snapshot;
        $steps = $dsl['steps'] ?? [];
        $step = $steps[$stepIndex] ?? null;

        if (!$step) {
            return;
        }

        // Support Parallel Gateways splits
        if (isset($step['type']) && $step['type'] === 'parallel_gateway') {
            $branches = $step['branches'] ?? [];
            foreach ($branches as $branch) {
                // Create parallel execution token
                WorkflowExecutionToken::create([
                    'id' => (string) Str::uuid(),
                    'workflow_instance_id' => $instance->id,
                    'gateway_id' => $step['name'],
                    'branch_name' => $branch['name'],
                    'status' => 'active',
                    'created_at' => now(),
                ]);

                // Spawn task for each parallel branch
                $dueAt = isset($branch['sla_hours']) ? now()->addHours((int) $branch['sla_hours']) : null;
                $task = WorkflowTask::create([
                    'id' => (string) Str::uuid(),
                    'instance_id' => $instance->id,
                    'step_name' => $step['name'],
                    'status' => TaskStatus::Pending,
                    'due_at' => $dueAt,
                ]);

                WorkflowTaskAssignment::create([
                    'id' => (string) Str::uuid(),
                    'task_id' => $task->id,
                    'assignment_type' => 'role',
                    'assignment_value' => $branch['role'] ?? 'admin',
                    'assigned_at' => now(),
                ]);

                // Register timeout events
                if (isset($branch['sla_hours'])) {
                    $this->scheduler->schedule(
                        category: 'workflow',
                        jobType: 'timeout',
                        aggregateType: WorkflowTask::class,
                        aggregateId: $task->id,
                        executeAt: now()->addHours((int) $branch['sla_hours']),
                        payload: [
                            'task_id' => $task->id,
                            'action' => $branch['timeout_action'] ?? 'reject',
                            'comments' => 'Auto-processed by parallel SLA timeout.',
                        ]
                    );
                }
            }
            return;
        }

        // Standard sequential step task spawning
        $dueAt = isset($step['sla_hours']) ? now()->addHours((int) $step['sla_hours']) : null;

        $task = WorkflowTask::create([
            'id' => (string) Str::uuid(),
            'instance_id' => $instance->id,
            'step_name' => $step['name'],
            'status' => TaskStatus::Pending,
            'due_at' => $dueAt,
        ]);

        $role = $step['role'] ?? 'admin';
        $approvalMode = $step['approval_mode'] ?? 'any';

        if ($approvalMode === 'all') {
            $users = User::whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            })->get();

            if ($users->isEmpty()) {
                WorkflowTaskAssignment::create([
                    'id' => (string) Str::uuid(),
                    'task_id' => $task->id,
                    'assignment_type' => 'role',
                    'assignment_value' => $role,
                    'assigned_at' => now(),
                ]);
            } else {
                $task->update(['status' => TaskStatus::Assigned]);

                foreach ($users as $user) {
                    WorkflowTaskAssignment::create([
                        'id' => (string) Str::uuid(),
                        'task_id' => $task->id,
                        'assignment_type' => 'user',
                        'assignment_value' => $user->id,
                        'assigned_at' => now(),
                    ]);
                }
            }
        } else {
            WorkflowTaskAssignment::create([
                'id' => (string) Str::uuid(),
                'task_id' => $task->id,
                'assignment_type' => 'role',
                'assignment_value' => $role,
                'assigned_at' => now(),
            ]);
        }

        // Register standard timeout events
        if (isset($step['sla_hours'])) {
            $this->scheduler->schedule(
                category: 'workflow',
                jobType: 'timeout',
                aggregateType: WorkflowTask::class,
                aggregateId: $task->id,
                executeAt: now()->addHours((int) $step['sla_hours']),
                payload: [
                    'task_id' => $task->id,
                    'action' => $step['timeout_action'] ?? 'reject',
                    'comments' => 'Auto-processed by sequential SLA timeout.',
                ]
            );
        }
    }

    /**
     * Escalate overdue tasks.
     */
    public function escalateOverdueTasks(): void
    {
        DB::transaction(function () {
            $overdueTasks = WorkflowTask::where('status', TaskStatus::Pending)
                ->whereNotNull('due_at')
                ->where('due_at', '<=', now())
                ->get();

            foreach ($overdueTasks as $task) {
                $task->update([
                    'status' => TaskStatus::Escalated,
                ]);

                WorkflowTaskAssignment::create([
                    'id' => (string) Str::uuid(),
                    'task_id' => $task->id,
                    'assignment_type' => 'role',
                    'assignment_value' => 'super_admin',
                    'assigned_at' => now(),
                ]);

                WorkflowHistory::create([
                    'id' => (string) Str::uuid(),
                    'instance_id' => $task->instance_id,
                    'task_id' => $task->id,
                    'from_state' => $task->instance->current_state,
                    'to_state' => $task->instance->current_state,
                    'action' => 'escalate',
                    'comments' => "Task SLA breached. Escalated step [{$task->step_name}] to super_admin.",
                    'actioned_by' => null,
                    'created_at' => now(),
                ]);
            }
        });
    }
}
