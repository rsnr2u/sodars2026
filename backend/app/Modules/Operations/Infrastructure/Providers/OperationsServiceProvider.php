<?php

declare(strict_types=1);

namespace App\Modules\Operations\Infrastructure\Providers;

use App\Modules\Operations\Domain\Services\OperationsLifecycleService;
use App\Modules\Operations\Domain\Services\OperationsMetricsEngine;
use App\Modules\Operations\Domain\Services\ConflictDetectionEngine;
use App\Modules\Operations\Domain\Services\ETAEngine;
use App\Modules\Operations\Domain\Services\RecurrenceEngine;
use App\Modules\Operations\Domain\Services\AssignmentEngine;
use App\Modules\Operations\Domain\Services\AvailabilityEngine;
use App\Modules\Operations\Domain\Services\CapacityEngine;
use App\Modules\Operations\Domain\Services\OptimizationEngine;
use App\Modules\Operations\Domain\Managers\ScheduleLifecycleManager;
use App\Modules\Operations\Domain\Managers\ShiftLifecycleManager;
use App\Modules\Operations\Domain\Managers\CalendarLifecycleManager;
use App\Modules\Operations\Domain\Managers\ResourceLifecycleManager;
use App\Platform\Identifiers\ScheduleNumberGenerator;
use Illuminate\Support\ServiceProvider;

class OperationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ScheduleNumberGenerator::class);
        $this->app->singleton(ScheduleLifecycleManager::class);
        $this->app->singleton(ShiftLifecycleManager::class);
        $this->app->singleton(CalendarLifecycleManager::class);
        $this->app->singleton(ResourceLifecycleManager::class);

        $this->app->singleton(RecurrenceEngine::class);
        $this->app->singleton(AvailabilityEngine::class);
        $this->app->singleton(CapacityEngine::class);
        $this->app->singleton(ConflictDetectionEngine::class);
        $this->app->singleton(ETAEngine::class);
        $this->app->singleton(AssignmentEngine::class);
        $this->app->singleton(OptimizationEngine::class);

        $this->app->singleton(OperationsMetricsEngine::class);
        $this->app->singleton(OperationsLifecycleService::class);
    }

    public function boot(): void
    {
        // Load API routes
        if (file_exists(__DIR__ . '/../../Presentation/Routes/v1/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/v1/api.php');
        }
    }
}
