<?php

declare(strict_types=1);

use App\Modules\Transport\Presentation\Controllers\TransportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/v1/transport')->group(function () {
    // Vehicle operations
    Route::post('vehicles', [TransportController::class, 'createVehicle']);
    Route::get('vehicles/{id}', [TransportController::class, 'showVehicle']);
    Route::post('vehicles/{id}/maintenance', [TransportController::class, 'logMaintenance']);
    Route::post('vehicles/{id}/fuel', [TransportController::class, 'logFuel']);
    Route::post('vehicles/{id}/assign', [TransportController::class, 'assignDriver']);
    Route::post('vehicles/{id}/release', [TransportController::class, 'releaseDriver']);
    Route::post('vehicles/{id}/gps', [TransportController::class, 'logGPS']);

    // Driver operations
    Route::post('drivers', [TransportController::class, 'createDriver']);

    // Route operations
    Route::post('routes', [TransportController::class, 'createRoute']);
    Route::post('routes/{id}/assign', [TransportController::class, 'assignRoute']);
    Route::post('routes/{id}/dispatch', [TransportController::class, 'dispatchRoute']);
    Route::post('routes/{id}/status', [TransportController::class, 'changeRouteStatus']);
});
