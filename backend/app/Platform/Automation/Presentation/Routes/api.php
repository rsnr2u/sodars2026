<?php

declare(strict_types=1);

use App\Platform\Automation\Presentation\Controllers\AutomationRuleController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    // Automation Rules
    Route::get('automation/rules', [AutomationRuleController::class, 'index']);
    Route::post('automation/rules', [AutomationRuleController::class, 'store']);
    Route::get('automation/rules/{id}', [AutomationRuleController::class, 'show']);
    Route::put('automation/rules/{id}', [AutomationRuleController::class, 'update']);
    Route::delete('automation/rules/{id}', [AutomationRuleController::class, 'destroy']);
});
