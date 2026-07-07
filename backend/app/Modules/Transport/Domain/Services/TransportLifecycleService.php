<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Services;

use App\Core\Services\OutboxService;
use App\Core\Context\TraceContext;
use App\Modules\Transport\Domain\Entities\Vehicle;
use App\Modules\Transport\Domain\Entities\VehicleMaintenance;
use App\Modules\Transport\Domain\Entities\VehicleFuelLog;
use App\Modules\Transport\Domain\Entities\VehicleAssignment;
use App\Modules\Transport\Domain\Entities\Driver;
use App\Modules\Transport\Domain\Entities\Route;
use App\Modules\Transport\Domain\Enums\VehicleStatus;
use App\Modules\Transport\Domain\Enums\DriverStatus;
use App\Modules\Transport\Domain\Enums\RouteStatus;
use App\Modules\Transport\Domain\Events\VehicleCreated;
use App\Modules\Transport\Domain\Events\VehicleUpdated;
use App\Modules\Transport\Domain\Events\VehicleStatusChanged;
use App\Modules\Transport\Domain\Events\VehicleAssigned;
use App\Modules\Transport\Domain\Events\VehicleReleased;
use App\Modules\Transport\Domain\Events\MaintenanceScheduled;
use App\Modules\Transport\Domain\Events\MaintenanceCompleted;
use App\Modules\Transport\Domain\Events\VehicleFuelLogged;
use App\Modules\Transport\Domain\Events\DriverCreated;
use App\Modules\Transport\Domain\Events\DriverUpdated;
use App\Modules\Transport\Domain\Events\DriverSuspended;
use App\Modules\Transport\Domain\Events\RouteCreated;
use App\Modules\Transport\Domain\Events\RouteDispatched;
use App\Modules\Transport\Domain\Events\RouteStatusChanged;
use App\Modules\Transport\Domain\Events\RouteCompleted;
use App\Modules\Transport\Domain\Events\RouteCancelled;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class TransportLifecycleService
{
    public function __construct(
        protected OutboxService $outboxService,
        protected VehicleLifecycleManager $vehicleManager,
        protected DriverLifecycleManager $driverManager,
        protected RouteLifecycleManager $routeManager
    ) {}

    protected function getActorId(): ?string
    {
        return Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) \App\Platform\Identity\Application\Services\IdentityContext::userId() : null);
    }

    // ─── Vehicle Operations ──────────────────────────────────────

    public function createVehicle(array $attributes): Vehicle
    {
        $vehicle = $this->vehicleManager->createVehicle($attributes);
        $data = $vehicle->toArray();

        $this->outboxService->record(
            aggregateType: 'Vehicle',
            aggregateId: $vehicle->id,
            eventName: 'transport.vehicle.created.v1',
            data: $data
        );

        Event::dispatch(new VehicleCreated(
            aggregateId: $vehicle->id,
            aggregateVersion: 1,
            data: $data,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: $this->getActorId()
        ));

        return $vehicle;
    }

    public function updateVehicle(Vehicle $vehicle, array $attributes): void
    {
        $this->vehicleManager->updateVehicle($vehicle, $attributes);
        $data = $vehicle->toArray();

        $this->outboxService->record(
            aggregateType: 'Vehicle',
            aggregateId: $vehicle->id,
            eventName: 'transport.vehicle.updated.v1',
            data: $data
        );

        Event::dispatch(new VehicleUpdated(
            aggregateId: $vehicle->id,
            aggregateVersion: 1,
            data: $data,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: $this->getActorId()
        ));
    }

    public function changeVehicleStatus(Vehicle $vehicle, VehicleStatus $status): void
    {
        $this->vehicleManager->changeStatus($vehicle, $status);
        $data = $vehicle->toArray();

        $this->outboxService->record(
            aggregateType: 'Vehicle',
            aggregateId: $vehicle->id,
            eventName: 'transport.vehicle.status_changed.v1',
            data: $data
        );

        Event::dispatch(new VehicleStatusChanged(
            aggregateId: $vehicle->id,
            aggregateVersion: 1,
            data: $data,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: $this->getActorId()
        ));
    }

    public function logMaintenance(Vehicle $vehicle, array $data): VehicleMaintenance
    {
        $maintenance = $this->vehicleManager->logMaintenance($vehicle, $data);
        $logData = $maintenance->toArray();

        $eventName = $maintenance->status === 'Completed'
            ? 'transport.vehicle.maintenance.completed.v1'
            : 'transport.vehicle.maintenance.scheduled.v1';

        $this->outboxService->record(
            aggregateType: 'Vehicle',
            aggregateId: $vehicle->id,
            eventName: $eventName,
            data: $logData
        );

        $eventClass = $maintenance->status === 'Completed'
            ? MaintenanceCompleted::class
            : MaintenanceScheduled::class;

        Event::dispatch(new $eventClass(
            aggregateId: $vehicle->id,
            aggregateVersion: 1,
            data: $logData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: $this->getActorId()
        ));

        return $maintenance;
    }

    public function logFuel(Vehicle $vehicle, array $data): VehicleFuelLog
    {
        $fuel = $this->vehicleManager->logFuel($vehicle, $data);
        $fuelData = $fuel->toArray();

        $this->outboxService->record(
            aggregateType: 'Vehicle',
            aggregateId: $vehicle->id,
            eventName: 'transport.vehicle.fuel_logged.v1',
            data: $fuelData
        );

        Event::dispatch(new VehicleFuelLogged(
            aggregateId: $vehicle->id,
            aggregateVersion: 1,
            data: $fuelData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: $this->getActorId()
        ));

        return $fuel;
    }

    public function assignDriver(Vehicle $vehicle, string $driverId, string $reason = null): VehicleAssignment
    {
        $assignment = $this->vehicleManager->assignDriver($vehicle, $driverId, $reason);
        $assignData = $assignment->toArray();

        $this->outboxService->record(
            aggregateType: 'Vehicle',
            aggregateId: $vehicle->id,
            eventName: 'transport.vehicle.assigned.v1',
            data: $assignData
        );

        Event::dispatch(new VehicleAssigned(
            aggregateId: $vehicle->id,
            aggregateVersion: 1,
            data: $assignData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: $this->getActorId()
        ));

        return $assignment;
    }

    public function releaseDriver(Vehicle $vehicle): void
    {
        $this->vehicleManager->releaseDriver($vehicle);
        $data = ['vehicle_id' => $vehicle->id];

        $this->outboxService->record(
            aggregateType: 'Vehicle',
            aggregateId: $vehicle->id,
            eventName: 'transport.vehicle.released.v1',
            data: $data
        );

        Event::dispatch(new VehicleReleased(
            aggregateId: $vehicle->id,
            aggregateVersion: 1,
            data: $data,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: $this->getActorId()
        ));
    }

    // ─── Driver Operations ───────────────────────────────────────

    public function createDriver(array $attributes): Driver
    {
        $driver = $this->driverManager->createDriver($attributes);
        $data = $driver->toArray();

        $this->outboxService->record(
            aggregateType: 'Driver',
            aggregateId: $driver->id,
            eventName: 'transport.driver.created.v1',
            data: $data
        );

        Event::dispatch(new DriverCreated(
            aggregateId: $driver->id,
            aggregateVersion: 1,
            data: $data,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: $this->getActorId()
        ));

        return $driver;
    }

    public function updateDriver(Driver $driver, array $attributes): void
    {
        $this->driverManager->updateDriver($driver, $attributes);
        $data = $driver->toArray();

        $this->outboxService->record(
            aggregateType: 'Driver',
            aggregateId: $driver->id,
            eventName: 'transport.driver.updated.v1',
            data: $data
        );

        Event::dispatch(new DriverUpdated(
            aggregateId: $driver->id,
            aggregateVersion: 1,
            data: $data,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: $this->getActorId()
        ));
    }

    public function suspendDriver(Driver $driver): void
    {
        $this->driverManager->suspendDriver($driver);
        $data = $driver->toArray();

        $this->outboxService->record(
            aggregateType: 'Driver',
            aggregateId: $driver->id,
            eventName: 'transport.driver.suspended.v1',
            data: $data
        );

        Event::dispatch(new DriverSuspended(
            aggregateId: $driver->id,
            aggregateVersion: 1,
            data: $data,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: $this->getActorId()
        ));
    }

    // ─── Route Operations ────────────────────────────────────────

    public function createRoute(array $attributes): Route
    {
        $route = $this->routeManager->createRoute($attributes);
        $data = $route->toArray();

        $this->outboxService->record(
            aggregateType: 'Route',
            aggregateId: $route->id,
            eventName: 'transport.route.created.v1',
            data: $data
        );

        Event::dispatch(new RouteCreated(
            aggregateId: $route->id,
            aggregateVersion: 1,
            data: $data,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: $this->getActorId()
        ));

        return $route;
    }

    public function assignRoute(Route $route, string $vehicleId, string $driverId): void
    {
        $this->routeManager->assignRoute($route, $vehicleId, $driverId);
        $data = $route->toArray();

        $this->outboxService->record(
            aggregateType: 'Route',
            aggregateId: $route->id,
            eventName: 'transport.route.status_changed.v1',
            data: $data
        );

        Event::dispatch(new RouteStatusChanged(
            aggregateId: $route->id,
            aggregateVersion: 1,
            data: $data,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: $this->getActorId()
        ));
    }

    public function dispatchRoute(Route $route): void
    {
        $this->routeManager->dispatchRoute($route);
        $data = $route->toArray();

        $this->outboxService->record(
            aggregateType: 'Route',
            aggregateId: $route->id,
            eventName: 'transport.route.dispatched.v1',
            data: $data
        );

        Event::dispatch(new RouteDispatched(
            aggregateId: $route->id,
            aggregateVersion: 1,
            data: $data,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: $this->getActorId()
        ));
    }

    public function changeRouteStatus(Route $route, RouteStatus $status, array $metrics = []): void
    {
        $this->routeManager->updateStatus($route, $status, $metrics);
        $data = $route->toArray();

        $eventName = $status === RouteStatus::Completed
            ? 'transport.route.completed.v1'
            : 'transport.route.status_changed.v1';

        $this->outboxService->record(
            aggregateType: 'Route',
            aggregateId: $route->id,
            eventName: $eventName,
            data: $data
        );

        $eventClass = $status === RouteStatus::Completed
            ? RouteCompleted::class
            : RouteStatusChanged::class;

        Event::dispatch(new $eventClass(
            aggregateId: $route->id,
            aggregateVersion: 1,
            data: $data,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: $this->getActorId()
        ));
    }

    public function cancelRoute(Route $route): void
    {
        $this->routeManager->cancelRoute($route);
        $data = $route->toArray();

        $this->outboxService->record(
            aggregateType: 'Route',
            aggregateId: $route->id,
            eventName: 'transport.route.cancelled.v1',
            data: $data
        );

        Event::dispatch(new RouteCancelled(
            aggregateId: $route->id,
            aggregateVersion: 1,
            data: $data,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: $this->getActorId()
        ));
    }
}
