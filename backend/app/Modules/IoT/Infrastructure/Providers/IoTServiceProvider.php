<?php

declare(strict_types=1);

namespace App\Modules\IoT\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\IoT\Domain\Services\DeviceLifecycleService;
use App\Modules\IoT\Domain\Managers\DeviceLifecycleManager;
use App\Modules\IoT\Domain\Managers\TelemetryLifecycleManager;
use App\Modules\IoT\Domain\Managers\FirmwareLifecycleManager;
use App\Modules\IoT\Domain\Managers\CommandLifecycleManager;
use App\Modules\IoT\Domain\Services\TelemetryProcessor;
use App\Modules\IoT\Domain\Services\HmacAuthenticator;
use App\Modules\IoT\Domain\Services\DeviceMetricsEngine;
use App\Platform\Scheduler\Application\Services\SchedulerDispatcher;
use App\Modules\IoT\Application\Services\Handlers\OfflineDetectionHandler;
use App\Modules\IoT\Application\Services\Handlers\FirmwareDeploymentHandler;
use App\Modules\IoT\Application\Services\Handlers\RetryDeviceCommandHandler;
use App\Modules\IoT\Application\Services\Handlers\HeartbeatCleanupHandler;
use App\Modules\IoT\Application\Services\Handlers\TelemetryRetentionHandler;
use App\Modules\IoT\Application\Services\Handlers\HealthAggregationHandler;
use App\Modules\IoT\Application\Services\Handlers\AlertEscalationHandler;

class IoTServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeviceLifecycleManager::class);
        $this->app->singleton(TelemetryLifecycleManager::class);
        $this->app->singleton(FirmwareLifecycleManager::class);
        $this->app->singleton(CommandLifecycleManager::class);

        $this->app->singleton(DeviceLifecycleService::class, function ($app) {
            return new DeviceLifecycleService(
                $app->make(DeviceLifecycleManager::class),
                $app->make(TelemetryLifecycleManager::class),
                $app->make(FirmwareLifecycleManager::class),
                $app->make(CommandLifecycleManager::class)
            );
        });

        $this->app->singleton(TelemetryProcessor::class);
        $this->app->singleton(HmacAuthenticator::class);
        $this->app->singleton(DeviceMetricsEngine::class);
    }

    public function boot(): void
    {
        // Register IoT handlers in Platform Scheduler Dispatcher
        if ($this->app->bound(SchedulerDispatcher::class)) {
            $dispatcher = $this->app->make(SchedulerDispatcher::class);
            $dispatcher->register('offline_detection', OfflineDetectionHandler::class);
            $dispatcher->register('firmware_deployment', FirmwareDeploymentHandler::class);
            $dispatcher->register('retry_command', RetryDeviceCommandHandler::class);
            $dispatcher->register('heartbeat_cleanup', HeartbeatCleanupHandler::class);
            $dispatcher->register('telemetry_retention', TelemetryRetentionHandler::class);
            $dispatcher->register('health_aggregation', HealthAggregationHandler::class);
            $dispatcher->register('alert_escalation', AlertEscalationHandler::class);
        }
    }
}
