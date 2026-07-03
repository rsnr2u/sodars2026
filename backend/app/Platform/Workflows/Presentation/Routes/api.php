<?php

declare(strict_types=1);

use App\Platform\Workflows\Presentation\Controllers\WorkflowTaskController;
use App\Platform\Workflows\Presentation\Controllers\WorkflowInstanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    // Tasks
    Route::get('workflows/tasks', [WorkflowTaskController::class, 'index']);
    Route::post('workflows/tasks/{id}/action', [WorkflowTaskController::class, 'action']);

    // Instances
    Route::get('workflows/instances/{id}', [WorkflowInstanceController::class, 'show']);
});
