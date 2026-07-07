<?php

declare(strict_types=1);

namespace App\Platform\Scheduler\Application\Services;

use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use RuntimeException;

class SchedulerDispatcher
{
    protected array $handlers = [];

    /**
     * Register a job type to its corresponding handler class.
     */
    public function register(string $jobType, string $handlerClass): void
    {
        $this->handlers[$jobType] = $handlerClass;
    }

    /**
     * Resolve and dispatch a scheduled job to its registered handler.
     */
    public function dispatch(ScheduledJob $job): void
    {
        $type = $job->job_type;
        if (!isset($this->handlers[$type])) {
            throw new RuntimeException("No scheduler handler registered for job type: {$type}");
        }

        $handler = app($this->handlers[$type]);
        if (!$handler instanceof ScheduledJobHandler) {
            throw new RuntimeException("Handler class {$this->handlers[$type]} must implement ScheduledJobHandler interface.");
        }

        $handler->handle($job);
    }
}
