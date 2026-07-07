<?php

declare(strict_types=1);

namespace App\Modules\Transport\Application\Services;

use App\Modules\Transport\Domain\Entities\Vehicle;
use App\Modules\Transport\Domain\Entities\VehicleMaintenance;
use App\Modules\Transport\Domain\Entities\VehicleFuelLog;
use App\Modules\Transport\Domain\Entities\VehicleAssignment;
use App\Modules\Transport\Domain\Entities\VehicleGPSLog;
use App\Modules\Transport\Domain\Entities\Driver;
use App\Modules\Transport\Domain\Entities\Route;
use App\Modules\Transport\Domain\Enums\VehicleStatus;
use App\Modules\Transport\Domain\Enums\RouteStatus;
use App\Modules\Transport\Domain\Services\TransportLifecycleService;
use App\Platform\Identity\Application\Services\IdentityContext;
use Illuminate\Support\Str;

class TransportService
{
    public function __construct(
        protected TransportLifecycleService $lifecycleService
    ) {}

    // ─── Vehicle Services ────────────────────────────────────────

    public function createVehicle(array $attributes): Vehicle
    {
        $orgId = IdentityContext::organizationId();
        
        // Generate VEH number if not provided
        if (empty($attributes['vehicle_number'])) {
            $count = Vehicle::withTrashed()->count() + 1;
            $attributes['vehicle_number'] = 'VEH-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
        }

        $attributes['id'] = (string) Str::uuid();
        $attributes['organization_id'] = $orgId;
        if (empty($attributes['status'])) {
            $attributes['status'] = 'active';
        }

        return $this->lifecycleService->createVehicle($attributes)->refresh();
    }

    public function updateVehicle(string $id, array $attributes): Vehicle
    {
        $vehicle = Vehicle::findOrFail($id);
        $this->lifecycleService->updateVehicle($vehicle, $attributes);
        return $vehicle->refresh();
    }

    public function changeVehicleStatus(string $id, VehicleStatus $status): Vehicle
    {
        $vehicle = Vehicle::findOrFail($id);
        $this->lifecycleService->changeVehicleStatus($vehicle, $status);
        return $vehicle->refresh();
    }

    public function logMaintenance(string $vehicleId, array $data): VehicleMaintenance
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        
        $data['id'] = (string) Str::uuid();
        $data['organization_id'] = $vehicle->organization_id;
        
        return $this->lifecycleService->logMaintenance($vehicle, $data);
    }

    public function logFuel(string $vehicleId, array $data): VehicleFuelLog
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        $data['id'] = (string) Str::uuid();
        $data['organization_id'] = $vehicle->organization_id;

        return $this->lifecycleService->logFuel($vehicle, $data);
    }

    public function assignDriver(string $vehicleId, string $driverId, string $reason = null): VehicleAssignment
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        return $this->lifecycleService->assignDriver($vehicle, $driverId, $reason);
    }

    public function releaseDriver(string $vehicleId): void
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        $this->lifecycleService->releaseDriver($vehicle);
    }

    // ─── Driver Services ─────────────────────────────────────────

    public function createDriver(array $attributes): Driver
    {
        $orgId = IdentityContext::organizationId();

        // Generate DRV number if not provided
        if (empty($attributes['driver_number'])) {
            $count = Driver::withTrashed()->count() + 1;
            $attributes['driver_number'] = 'DRV-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
        }

        $attributes['id'] = (string) Str::uuid();
        $attributes['organization_id'] = $orgId;
        if (empty($attributes['status'])) {
            $attributes['status'] = 'active';
        }

        return $this->lifecycleService->createDriver($attributes)->refresh();
    }

    public function updateDriver(string $id, array $attributes): Driver
    {
        $driver = Driver::findOrFail($id);
        $this->lifecycleService->updateDriver($driver, $attributes);
        return $driver->refresh();
    }

    public function suspendDriver(string $id): Driver
    {
        $driver = Driver::findOrFail($id);
        $this->lifecycleService->suspendDriver($driver);
        return $driver->refresh();
    }

    // ─── Route Services ──────────────────────────────────────────

    public function createRoute(array $attributes): Route
    {
        $orgId = IdentityContext::organizationId();

        // Generate ROT number if not provided
        if (empty($attributes['route_number'])) {
            $count = Route::withTrashed()->count() + 1;
            $attributes['route_number'] = 'ROT-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
        }

        $attributes['id'] = (string) Str::uuid();
        $attributes['organization_id'] = $orgId;
        if (empty($attributes['status'])) {
            $attributes['status'] = 'Draft';
        }

        return $this->lifecycleService->createRoute($attributes)->refresh();
    }

    public function assignRoute(string $routeId, string $vehicleId, string $driverId): Route
    {
        $route = Route::findOrFail($routeId);
        $this->lifecycleService->assignRoute($route, $vehicleId, $driverId);
        return $route->refresh();
    }

    public function dispatchRoute(string $routeId): Route
    {
        $route = Route::findOrFail($routeId);
        $this->lifecycleService->dispatchRoute($route);
        return $route->refresh();
    }

    public function changeRouteStatus(string $routeId, RouteStatus $status, array $metrics = []): Route
    {
        $route = Route::findOrFail($routeId);
        $this->lifecycleService->changeRouteStatus($route, $status, $metrics);
        return $route->refresh();
    }

    public function cancelRoute(string $routeId): Route
    {
        $route = Route::findOrFail($routeId);
        $this->lifecycleService->cancelRoute($route);
        return $route->refresh();
    }

    // ─── GPS Telemetry Stream (Decoupled write) ──────────────────

    public function logGPS(string $vehicleId, array $telemetry): VehicleGPSLog
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        $log = VehicleGPSLog::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $vehicle->organization_id,
            'vehicle_id' => $vehicle->id,
            'latitude' => $telemetry['latitude'],
            'longitude' => $telemetry['longitude'],
            'speed_kmh' => $telemetry['speed_kmh'],
            'heading' => $telemetry['heading'] ?? null,
            'altitude' => $telemetry['altitude'] ?? null,
            'accuracy' => $telemetry['accuracy'] ?? null,
            'engine_status' => $telemetry['engine_status'] ?? 'on',
            'ignition_status' => $telemetry['ignition_status'] ?? 'on',
            'battery_voltage' => $telemetry['battery_voltage'] ?? null,
            'satellite_count' => $telemetry['satellite_count'] ?? null,
            'recorded_at' => $telemetry['recorded_at'] ?? now()->toDateTimeString(),
        ]);

        return $log;
    }
}
