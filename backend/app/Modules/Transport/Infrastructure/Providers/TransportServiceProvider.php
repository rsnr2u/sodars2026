<?php

declare(strict_types=1);

namespace App\Modules\Transport\Infrastructure\Providers;

use App\Modules\Transport\Domain\Entities\Vehicle;
use App\Modules\Transport\Presentation\Policies\VehiclePolicy;
use App\Modules\Transport\Domain\Services\VehicleLifecycleManager;
use App\Modules\Transport\Domain\Services\DriverLifecycleManager;
use App\Modules\Transport\Domain\Services\RouteLifecycleManager;
use App\Modules\Transport\Domain\Services\TransportLifecycleService;
use App\Modules\Transport\Application\Services\TransportService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class TransportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(VehicleLifecycleManager::class);
        $this->app->singleton(DriverLifecycleManager::class);
        $this->app->singleton(RouteLifecycleManager::class);
        $this->app->singleton(TransportLifecycleService::class);
        $this->app->singleton(TransportService::class);
    }

    public function boot(): void
    {
        Gate::policy(Vehicle::class, VehiclePolicy::class);

        if (file_exists(__DIR__ . '/../../Presentation/Routes/v1/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/v1/api.php');
        }

        if ($this->app->bound(\App\Platform\Workflows\Infrastructure\Registry\WorkflowRegistry::class)) {
            $registry = $this->app->make(\App\Platform\Workflows\Infrastructure\Registry\WorkflowRegistry::class);
            $registry->register(
                \App\Modules\Transport\Domain\Entities\Route::class,
                \App\Modules\Transport\Infrastructure\Workflows\RouteWorkflowHandler::class
            );
        }

        // Listen for IoT telemetry processed events
        \Illuminate\Support\Facades\Event::listen(
            \App\Modules\IoT\Domain\Events\DeviceTelemetryProcessed::class,
            \App\Modules\Transport\Application\Listeners\TransportTelemetryListener::class
        );
    }
}
