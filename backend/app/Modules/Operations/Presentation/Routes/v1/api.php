<?php

declare(strict_types=1);

use App\Modules\Operations\Presentation\Controllers\ScheduleController;
use App\Modules\Operations\Presentation\Controllers\ResourceController;
use App\Modules\Operations\Presentation\Controllers\ShiftController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/operations')->group(function () {
    // Schedules
    Route::get('schedules', [ScheduleController::class, 'index']);
    Route::post('schedules', [ScheduleController::class, 'store']);
    Route::get('schedules/metrics', [ScheduleController::class, 'metrics']);
    Route::get('schedules/{schedule}', [ScheduleController::class, 'show']);
    Route::post('schedules/{schedule}/transition', [ScheduleController::class, 'transition']);
    Route::post('schedules/{schedule}/telemetry', [ScheduleController::class, 'telemetry']);
    Route::post('schedules/{schedule}/recurrences', [ScheduleController::class, 'recurrences']);

    // Resources
    Route::get('resources', [ResourceController::class, 'index']);
    Route::post('resources', [ResourceController::class, 'store']);
    Route::post('schedules/{schedule}/assign', [ResourceController::class, 'assign']);
    Route::post('schedules/{schedule}/release', [ResourceController::class, 'release']);
    Route::post('resources/optimize', [ResourceController::class, 'optimize']);

    // Shifts & Calendars
    Route::get('shifts', [ShiftController::class, 'index']);
    Route::post('shifts', [ShiftController::class, 'store']);
    Route::post('calendars', [ShiftController::class, 'storeCalendar']);
});
