<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Platform\Workflows\Domain\Entities\WorkflowInstance;
use Illuminate\Http\JsonResponse;

class WorkflowInstanceController extends BaseApiController
{
    /**
     * Retrieve a workflow instance with its full execution history.
     */
    public function show(string $id): JsonResponse
    {
        $instance = WorkflowInstance::with(['definition', 'tasks.step', 'histories'])->findOrFail($id);

        $data = [
            'id' => $instance->id,
            'definition_name' => $instance->definition?->name,
            'definition_key' => $instance->definition?->key,
            'definition_version' => $instance->definition?->version,
            'entity_type' => $instance->entity_type,
            'entity_id' => $instance->entity_id,
            'status' => $instance->status->value,
            'current_step_index' => $instance->current_step_index,
            'context_snapshot' => $instance->context_snapshot,
            'started_at' => $instance->started_at?->toIso8601String(),
            'completed_at' => $instance->completed_at?->toIso8601String(),
            'tasks' => $instance->tasks->map(fn ($task) => [
                'id' => $task->id,
                'step_name' => $task->step?->name ?? $task->step_name,
                'status' => $task->status->value,
                'assigned_role' => $task->assigned_role,
                'assigned_user_id' => $task->assigned_user_id,
                'actioned_by' => $task->actioned_by,
                'actioned_at' => $task->actioned_at?->toIso8601String(),
                'comments' => $task->comments,
                'due_at' => $task->due_at?->toIso8601String(),
                'escalated_to_role' => $task->escalated_to_role,
                'escalated_at' => $task->escalated_at?->toIso8601String(),
            ]),
            'history' => $instance->histories->map(fn ($history) => [
                'id' => $history->id,
                'from_status' => $history->from_status,
                'to_status' => $history->to_status,
                'action' => $history->action,
                'comments' => $history->comments,
                'actioned_by' => $history->actioned_by,
                'created_at' => $history->created_at?->toIso8601String(),
            ]),
        ];

        return $this->successResponse($data, 'Workflow instance details and history retrieved successfully.');
    }
}
