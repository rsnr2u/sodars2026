<?php

declare(strict_types=1);

namespace App\Modules\Transport\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Transport\Application\Services\TransportService;
use App\Modules\Transport\Domain\Entities\Vehicle;
use App\Modules\Transport\Domain\Enums\VehicleStatus;
use App\Modules\Transport\Domain\Enums\RouteStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransportController extends BaseApiController
{
    public function __construct(
        protected TransportService $service
    ) {}

    public function createVehicle(Request $request): JsonResponse
    {
        $vehicle = $this->service->createVehicle($request->all());
        return $this->successResponse($vehicle->toArray(), 'Vehicle created successfully.', 201);
    }

    public function showVehicle(string $id): JsonResponse
    {
        $vehicle = Vehicle::findOrFail($id);
        $this->authorize('view', $vehicle);

        return $this->successResponse($vehicle->toArray(), 'Vehicle details retrieved successfully.');
    }

    public function logMaintenance(string $id, Request $request): JsonResponse
    {
        $maintenance = $this->service->logMaintenance($id, $request->all());
        return $this->successResponse($maintenance->toArray(), 'Maintenance logged successfully.', 201);
    }

    public function logFuel(string $id, Request $request): JsonResponse
    {
        $fuel = $this->service->logFuel($id, $request->all());
        return $this->successResponse($fuel->toArray(), 'Fuel refill logged successfully.', 201);
    }

    public function assignDriver(string $id, Request $request): JsonResponse
    {
        $request->validate([
            'driver_id' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        $assignment = $this->service->assignDriver($id, $request->input('driver_id'), $request->input('reason'));
        return $this->successResponse($assignment->toArray(), 'Driver assigned to vehicle successfully.', 201);
    }

    public function releaseDriver(string $id): JsonResponse
    {
        $this->service->releaseDriver($id);
        return $this->successResponse(null, 'Driver released from vehicle successfully.');
    }

    public function logGPS(string $id, Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'speed_kmh' => 'required|numeric',
        ]);

        $log = $this->service->logGPS($id, $request->all());
        return $this->successResponse($log->toArray(), 'GPS telemetry logged successfully.', 201);
    }

    public function createDriver(Request $request): JsonResponse
    {
        $driver = $this->service->createDriver($request->all());
        return $this->successResponse($driver->toArray(), 'Driver profile created successfully.', 201);
    }

    public function createRoute(Request $request): JsonResponse
    {
        $route = $this->service->createRoute($request->all());
        return $this->successResponse($route->toArray(), 'Operational route created successfully.', 201);
    }

    public function assignRoute(string $id, Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|string',
            'driver_id' => 'required|string',
        ]);

        $route = $this->service->assignRoute($id, $request->input('vehicle_id'), $request->input('driver_id'));
        return $this->successResponse($route->toArray(), 'Route assigned successfully.');
    }

    public function dispatchRoute(string $id): JsonResponse
    {
        $route = $this->service->dispatchRoute($id);
        return $this->successResponse($route->toArray(), 'Route dispatched successfully.');
    }

    public function changeRouteStatus(string $id, Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|string',
            'metrics' => 'nullable|array',
        ]);

        $status = RouteStatus::from($request->input('status'));
        $metrics = $request->input('metrics', []);

        $route = $this->service->changeRouteStatus($id, $status, $metrics);
        return $this->successResponse($route->toArray(), 'Route status updated successfully.');
    }
}
