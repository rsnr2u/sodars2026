<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Infrastructure\Providers;

use App\Platform\Workflows\Application\Services\WorkflowEngineService;
use App\Platform\Workflows\Infrastructure\Registry\WorkflowRegistry;
use Illuminate\Support\ServiceProvider;

class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WorkflowRegistry::class, function () {
            return new WorkflowRegistry();
        });

        $this->app->singleton(WorkflowEngineService::class, function ($app) {
            return new WorkflowEngineService(
                $app->make(WorkflowRegistry::class)
            );
        });
    }

    public function boot(): void
    {
        // Load API Routes
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/api.php');
    }
}
