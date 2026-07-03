<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Platform\Workflows\Application\Services\WorkflowEngineService;
use App\Platform\Workflows\Domain\Entities\WorkflowTask;
use App\Platform\Workflows\Domain\Enums\TaskStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowTaskController extends BaseApiController
{
    public function __construct(
        protected WorkflowEngineService $engine
    ) {}

    /**
     * List all pending tasks assigned to the authenticated user's role or user ID.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $roles = $user->roles ? $user->roles->pluck('name')->toArray() : [];

        $tasks = WorkflowTask::where(function ($query) use ($user, $roles) {
            $query->whereIn('assigned_role', $roles)
                ->orWhere('assigned_user_id', $user->id);
        })
        ->whereIn('status', [TaskStatus::Pending, TaskStatus::Assigned, TaskStatus::Escalated])
        ->with(['instance', 'step'])
        ->get();

        $data = $tasks->map(fn ($task) => [
            'id' => $task->id,
            'instance_id' => $task->instance_id,
            'step_name' => $task->step?->name ?? $task->step_name,
            'status' => $task->status->value,
            'assigned_role' => $task->assigned_role,
            'assigned_user_id' => $task->assigned_user_id,
            'due_at' => $task->due_at?->toIso8601String(),
            'escalated_to_role' => $task->escalated_to_role,
            'escalated_at' => $task->escalated_at?->toIso8601String(),
            'entity_type' => $task->instance?->entity_type,
            'entity_id' => $task->instance?->entity_id,
            'context_snapshot' => $task->instance?->context_snapshot,
        ]);

        return $this->successResponse($data, 'Pending workflow tasks retrieved successfully.');
    }

    /**
     * Action a pending task.
     */
    public function action(string $id, Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|in:approve,reject,request_changes',
            'comments' => 'nullable|string|max:1000',
        ]);

        $action = $request->input('action');
        $comments = $request->input('comments');
        $userId = $request->user()->id;

        try {
            $this->engine->actionTask($id, $action, $userId, $comments);
            return $this->successResponse(null, "Task action [{$action}] processed successfully.");
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }
}
