<?php

declare(strict_types=1);

use App\Platform\Audit\Presentation\Controllers\AuditController;
use App\Platform\Identity\Infrastructure\Middleware\EnforceOrganizationScope;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->middleware(['auth:sanctum', EnforceOrganizationScope::class])
    ->group(function () {
        Route::get('compliance/audit', [AuditController::class, 'index']);
        Route::get('compliance/audit/risks', [AuditController::class, 'getHighRisks']);
        Route::get('compliance/audit/entity/{type}/{id}', [AuditController::class, 'getEntityTimeline']);
        Route::post('compliance/audit/export', [AuditController::class, 'export']);
    });
