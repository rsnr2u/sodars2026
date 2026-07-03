<?php

declare(strict_types=1);

use App\Modules\Wallet\Presentation\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/v1')->group(function () {
    Route::get('wallets/{id}', [WalletController::class, 'show']);
    Route::get('wallets/{id}/transactions', [WalletController::class, 'transactions']);
    Route::post('wallets/{id}/deposit', [WalletController::class, 'deposit']);
    Route::get('wallets/{id}/withdrawals', [WalletController::class, 'withdrawals']);
    Route::post('wallets/{id}/withdrawals', [WalletController::class, 'requestWithdrawal']);
    Route::post('wallets/{id}/transfer', [WalletController::class, 'transfer']);
    
    // Process withdrawal payouts (admin auth required via policies)
    Route::patch('withdrawals/{withdrawalId}/process', [WalletController::class, 'processWithdrawal']);
});
