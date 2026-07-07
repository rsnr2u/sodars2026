<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\IoT\Presentation\Controllers\DeviceController;
use App\Modules\IoT\Presentation\Controllers\TelemetryController;
use App\Modules\IoT\Presentation\Controllers\CommandController;
use App\Modules\IoT\Presentation\Controllers\FirmwareController;

Route::prefix('api/v1/iot')->group(function () {
    // Device Management
    Route::post('devices/register', [DeviceController::class, 'register']);
    Route::post('devices/{id}/activate', [DeviceController::class, 'activate']);
    Route::post('devices/{id}/assign', [DeviceController::class, 'assign']);
    Route::get('devices/metrics', [DeviceController::class, 'metrics']);

    // Ingest Pipeline (Enforces HMAC Signature Verification in the Controller)
    Route::post('devices/telemetry', [TelemetryController::class, 'telemetry']);
    Route::post('devices/heartbeat', [TelemetryController::class, 'heartbeat']);

    // Remote Commands Command Queue
    Route::post('devices/{id}/commands', [CommandController::class, 'queue']);
    Route::post('commands/{id}/acknowledge', [CommandController::class, 'acknowledge']);
    Route::post('commands/{id}/complete', [CommandController::class, 'complete']);
    Route::get('commands/{id}', [CommandController::class, 'status']);

    // Firmware Rollout Package Manager
    Route::post('firmware/publish', [FirmwareController::class, 'publish']);
    Route::post('devices/{id}/firmware', [FirmwareController::class, 'rollout']);
    Route::post('firmware/installations/{id}/rollback', [FirmwareController::class, 'rollback']);
});
