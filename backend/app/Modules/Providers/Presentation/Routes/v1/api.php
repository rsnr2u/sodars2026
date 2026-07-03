<?php

declare(strict_types=1);

use App\Modules\Providers\Presentation\Controllers\ProviderBankAccountController;
use App\Modules\Providers\Presentation\Controllers\ProviderController;
use App\Modules\Providers\Presentation\Controllers\ProviderDocumentController;
use App\Modules\Providers\Presentation\Controllers\ProviderStaffController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->group(function () {
    // Public provider registration
    Route::post('providers', [ProviderController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('providers', [ProviderController::class, 'index']);
        Route::get('providers/{id}', [ProviderController::class, 'show']);
        Route::patch('providers/{id}/status', [ProviderController::class, 'updateStatus']);
        Route::get('providers/{id}/dashboard', [ProviderController::class, 'dashboard']);

        // Compliance Verification Documents
        Route::post('providers/{id}/documents', [ProviderDocumentController::class, 'upload']);
        Route::patch('providers/{id}/documents/{docId}/audit', [ProviderDocumentController::class, 'audit']);

        // Payout Parameters
        Route::put('providers/{id}/bank-accounts', [ProviderBankAccountController::class, 'update']);

        // Staff Memberships
        Route::post('providers/{id}/staff', [ProviderStaffController::class, 'add']);
        Route::delete('providers/{id}/staff/{staffId}', [ProviderStaffController::class, 'remove']);
    });
});
