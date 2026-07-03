<?php

use App\Platform\Shared\Presentation\Controllers\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Health Check Routes
|--------------------------------------------------------------------------
|
| These routes expose system health at three levels:
|   /health/live    — Liveness probe (always public)
|   /health/ready   — Readiness probe (public, checks DB/cache/storage)
|   /health/details — Full diagnostics (restricted: super-admin or APP_DEBUG)
|
*/

Route::prefix('health')->group(function () {
    Route::get('/live', [HealthController::class, 'live']);
    Route::get('/ready', [HealthController::class, 'ready']);
    Route::get('/details', [HealthController::class, 'details']);
});
