<?php

declare(strict_types=1);

namespace App\Platform\Automation\Infrastructure\Providers;

use App\Platform\Automation\Application\Services\AutomationEngine;
use App\Platform\Automation\Application\Services\ExpressionCompiler;
use App\Platform\Automation\Application\Services\ExpressionEvaluator;
use App\Platform\Automation\Domain\Registry\ActionStrategyRegistry;
use App\Platform\Automation\Infrastructure\Listeners\AutomationEventListener;
use App\Platform\Automation\Infrastructure\Strategies\SendNotificationAction;
use App\Platform\Automation\Infrastructure\Strategies\StartWorkflowAction;
use App\Platform\Automation\Infrastructure\Strategies\UpdateStatusAction;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class AutomationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ActionStrategyRegistry::class, function ($app) {
            $registry = new ActionStrategyRegistry();
            // Register standard action keys
            $registry->register('notification.send', SendNotificationAction::class);
            $registry->register('workflow.start', StartWorkflowAction::class);
            $registry->register('status.update', UpdateStatusAction::class);
            return $registry;
        });

        $this->app->singleton(AutomationEngine::class, function ($app) {
            return new AutomationEngine(
                $app->make(ExpressionCompiler::class),
                $app->make(ExpressionEvaluator::class),
                $app->make(ActionStrategyRegistry::class)
            );
        });
    }

    public function boot(): void
    {
        // Load API Routes
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/api.php');

        // Subscribe dynamic automation event listeners
        $events = $this->app->make(Dispatcher::class);
        $listener = new AutomationEventListener($this->app->make(AutomationEngine::class));
        $listener->subscribe($events);
    }
}
