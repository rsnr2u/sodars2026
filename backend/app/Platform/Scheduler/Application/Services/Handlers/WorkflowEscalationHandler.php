<?php

declare(strict_types=1);

namespace App\Platform\Scheduler\Application\Services\Handlers;

use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use App\Platform\Scheduler\Application\Services\ScheduledJobHandler;
use App\Platform\Workflows\Application\Services\WorkflowEngineService;

class WorkflowEscalationHandler implements ScheduledJobHandler
{
    public function handle(ScheduledJob $job): void
    {
        // Resolve dynamically to prevent circular dependencies
        app(WorkflowEngineService::class)->escalateOverdueTasks();
    }
}
