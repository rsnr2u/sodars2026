<?php

declare(strict_types=1);

namespace App\Platform\Scheduler\Infrastructure\Providers;

use App\Platform\Scheduler\Application\Services\SchedulerDispatcher;
use App\Platform\Scheduler\Application\Services\SchedulerService;
use App\Platform\Scheduler\Application\Services\Handlers\WorkflowTimeoutHandler;
use App\Platform\Scheduler\Application\Services\Handlers\WorkflowEscalationHandler;
use App\Platform\Scheduler\Infrastructure\Retry\FixedRetryStrategy;
use App\Platform\Scheduler\Infrastructure\Retry\LinearRetryStrategy;
use App\Platform\Scheduler\Infrastructure\Retry\ExponentialRetryStrategy;
use Illuminate\Support\ServiceProvider;

class SchedulerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FixedRetryStrategy::class);
        $this->app->singleton(LinearRetryStrategy::class);
        $this->app->singleton(ExponentialRetryStrategy::class);

        $this->app->singleton(SchedulerDispatcher::class, function ($app) {
            $dispatcher = new SchedulerDispatcher();
            
            // Register default platform job handlers
            $dispatcher->register('timeout', WorkflowTimeoutHandler::class);
            $dispatcher->register('escalation', WorkflowEscalationHandler::class);
            
            return $dispatcher;
        });

        $this->app->singleton(SchedulerService::class);
    }
}
