<?php

declare(strict_types=1);

use App\Modules\Finance\Presentation\Controllers\InvoiceController;
use App\Modules\Finance\Presentation\Controllers\SettlementController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    // Invoices
    Route::get('invoices', [InvoiceController::class, 'index']);
    Route::get('invoices/revenue-analytics', [InvoiceController::class, 'getRevenueAnalytics']);
    Route::post('invoices/recognize-revenue', [InvoiceController::class, 'recognizeRevenue']);
    Route::get('invoices/{id}', [InvoiceController::class, 'show']);
    Route::post('invoices/{id}/issue', [InvoiceController::class, 'issue']);
    Route::post('invoices/{id}/payments', [InvoiceController::class, 'recordPayment']);
    Route::post('invoices/{id}/adjustments', [InvoiceController::class, 'recordAdjustment']);

    // Provider Settlements
    Route::get('settlements', [SettlementController::class, 'index']);
    Route::post('settlements/{id}/payout', [SettlementController::class, 'payout']);
});
