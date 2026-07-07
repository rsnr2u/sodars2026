<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Providers;

use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Repositories\InventoryReadRepositoryInterface;
use App\Modules\Inventory\Domain\Repositories\InventoryWriteRepositoryInterface;
use App\Modules\Inventory\Domain\Repositories\InventoryFaceRepositoryInterface;
use App\Modules\Inventory\Domain\Repositories\InventoryPricingRepositoryInterface;
use App\Modules\Inventory\Domain\Repositories\InventoryAvailabilityRepositoryInterface;
use App\Modules\Inventory\Infrastructure\Repositories\InventoryReadRepository;
use App\Modules\Inventory\Infrastructure\Repositories\InventoryWriteRepository;
use App\Modules\Inventory\Infrastructure\Repositories\InventoryFaceRepository;
use App\Modules\Inventory\Infrastructure\Repositories\InventoryPricingRepository;
use App\Modules\Inventory\Infrastructure\Repositories\InventoryAvailabilityRepository;
use App\Modules\Inventory\Presentation\Policies\InventoryPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    /**
     * Bind repository abstractions to concrete implementations.
     */
    public function register(): void
    {
        $this->app->bind(InventoryReadRepositoryInterface::class, InventoryReadRepository::class);
        $this->app->bind(InventoryWriteRepositoryInterface::class, InventoryWriteRepository::class);
        $this->app->bind(InventoryFaceRepositoryInterface::class, InventoryFaceRepository::class);
        $this->app->bind(InventoryPricingRepositoryInterface::class, InventoryPricingRepository::class);
        $this->app->bind(InventoryAvailabilityRepositoryInterface::class, InventoryAvailabilityRepository::class);
    }

    /**
     * Load routes and register authorization policies.
     */
    public function boot(): void
    {
        Gate::policy(Inventory::class, InventoryPolicy::class);

        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/v1/api.php');

        // Listen for IoT telemetry processed events
        \Illuminate\Support\Facades\Event::listen(
            \App\Modules\IoT\Domain\Events\DeviceTelemetryProcessed::class,
            \App\Modules\Inventory\Application\Listeners\InventoryTelemetryListener::class
        );
    }
}
