<?php

declare(strict_types=1);

use App\Modules\CRM\Presentation\Controllers\LeadController;
use App\Modules\CRM\Presentation\Controllers\QuotationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/v1')->group(function () {
    // Lead Management
    Route::get('/leads', [LeadController::class, 'index']);
    Route::post('/leads', [LeadController::class, 'store']);
    Route::post('/leads/{id}/qualify', [LeadController::class, 'qualify']);

    // Quotation Management
    Route::post('/quotations', [QuotationController::class, 'store']);
    Route::get('/quotations/{id}', [QuotationController::class, 'show']);
    Route::post('/quotations/{id}/convert', [QuotationController::class, 'convert']);
});
