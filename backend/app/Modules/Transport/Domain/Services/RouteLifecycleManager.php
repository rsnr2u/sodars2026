<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Services;

use App\Modules\Transport\Domain\Entities\Route;
use App\Modules\Transport\Domain\Enums\RouteStatus;
use Carbon\Carbon;

class RouteLifecycleManager
{
    public function createRoute(array $attributes): Route
    {
        $route = Route::create($attributes);
        return $route;
    }

    public function assignRoute(Route $route, string $vehicleId, string $driverId): void
    {
        $route->update([
            'vehicle_id' => $vehicleId,
            'driver_id' => $driverId,
            'status' => RouteStatus::Assigned,
        ]);
    }

    public function dispatchRoute(Route $route): void
    {
        $route->update([
            'status' => RouteStatus::Dispatched,
            'started_at' => Carbon::now(),
        ]);
    }

    public function updateStatus(Route $route, RouteStatus $status, array $metrics = []): void
    {
        $data = ['status' => $status];
        if (!empty($metrics)) {
            $data = array_merge($data, $metrics);
        }

        if ($status === RouteStatus::Completed) {
            $data['completed_at'] = Carbon::now();
        }

        $route->update($data);
    }

    public function cancelRoute(Route $route): void
    {
        $route->update(['status' => RouteStatus::Cancelled]);
    }
}
