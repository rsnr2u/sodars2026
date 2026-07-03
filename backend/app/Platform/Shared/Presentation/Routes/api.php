<?php

use App\Platform\Shared\Presentation\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->group(function (): void {
    Route::get('/health', [HealthController::class, 'ready']);
});
