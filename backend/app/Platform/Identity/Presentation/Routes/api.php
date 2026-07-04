<?php

declare(strict_types=1);

use App\Platform\Identity\Presentation\Controllers\OrganizationController;
use App\Platform\Identity\Presentation\Controllers\TeamController;
use App\Platform\Identity\Presentation\Controllers\SessionController;
use App\Platform\Identity\Presentation\Controllers\ActivityController;
use App\Platform\Identity\Infrastructure\Middleware\EnforceOrganizationScope;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->middleware(['auth:sanctum', EnforceOrganizationScope::class])
    ->group(function () {
        // Organizations
        Route::get('organizations', [OrganizationController::class, 'index']);
        Route::post('organizations', [OrganizationController::class, 'store']);
        Route::get('organizations/{id}', [OrganizationController::class, 'show']);
        Route::get('organizations/{id}/members', [OrganizationController::class, 'getMembers']);
        Route::post('organizations/{id}/members', [OrganizationController::class, 'addMember']);
        Route::delete('organizations/{id}/members/{userId}', [OrganizationController::class, 'removeMember']);

        // Teams
        Route::get('teams', [TeamController::class, 'index']);
        Route::post('teams', [TeamController::class, 'store']);
        Route::get('teams/{id}', [TeamController::class, 'show']);
        Route::post('teams/{id}/members', [TeamController::class, 'addMember']);
        Route::delete('teams/{id}/members/{userId}', [TeamController::class, 'removeMember']);

        // Login Sessions
        Route::get('sessions', [SessionController::class, 'index']);
        Route::delete('sessions/{id}', [SessionController::class, 'destroy']);
        Route::delete('sessions', [SessionController::class, 'revokeOthers']);

        // Activity Logs
        Route::get('activity', [ActivityController::class, 'index']);
        Route::get('activity/entity/{type}/{id}', [ActivityController::class, 'getEntityLogs']);
    });
