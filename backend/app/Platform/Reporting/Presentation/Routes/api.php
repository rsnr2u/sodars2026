<?php

declare(strict_types=1);

use App\Platform\Reporting\Presentation\Controllers\ReportController;
use App\Platform\Reporting\Presentation\Controllers\DashboardController;
use App\Platform\Reporting\Presentation\Controllers\ScheduledReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    // Report endpoints
    Route::get('reports', [ReportController::class, 'index']);
    Route::get('reports/{key}', [ReportController::class, 'show']);
    Route::post('reports/{key}/run', [ReportController::class, 'run']);
    Route::post('reports/{key}/export', [ReportController::class, 'export']);
    Route::get('reports/executions/{id}', [ReportController::class, 'getExecution']);

    // Dashboard endpoints
    Route::get('dashboards', [DashboardController::class, 'index']);
    Route::post('dashboards', [DashboardController::class, 'store']);
    Route::get('dashboards/{id}', [DashboardController::class, 'show']);
    Route::get('dashboards/{id}/widgets', [DashboardController::class, 'widgets']);
    Route::post('dashboards/{id}/widgets', [DashboardController::class, 'addWidget']);

    // Scheduled Report endpoints
    Route::get('reporting/scheduled', [ScheduledReportController::class, 'index']);
    Route::post('reporting/scheduled', [ScheduledReportController::class, 'store']);
    Route::post('reporting/scheduled/{id}/run', [ScheduledReportController::class, 'runNow']);
});
