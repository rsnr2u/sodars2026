<?php

declare(strict_types=1);

namespace App\Platform\Scheduler\Application\Services\Handlers;

use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use App\Platform\Scheduler\Application\Services\ScheduledJobHandler;
use App\Platform\Workflows\Application\Services\WorkflowEngineService;

class WorkflowTimeoutHandler implements ScheduledJobHandler
{
    public function handle(ScheduledJob $job): void
    {
        $taskId = $job->payload['task_id'] ?? null;
        if ($taskId) {
            $action = $job->payload['action'] ?? 'reject';
            $actorId = $job->payload['actor_id'] ?? 'system';
            $comments = $job->payload['comments'] ?? 'Auto-processed by timeout SLA.';

            // Resolve dynamically to prevent circular dependencies
            app(WorkflowEngineService::class)->actionTask($taskId, $action, $actorId, $comments);
        }
    }
}
