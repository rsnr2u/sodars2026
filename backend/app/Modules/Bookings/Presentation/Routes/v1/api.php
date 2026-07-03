<?php

declare(strict_types=1);

use App\Modules\Bookings\Presentation\Controllers\BookingController;
use App\Modules\Bookings\Presentation\Controllers\BookingPaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    Route::get('bookings', [BookingController::class, 'index']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings/dashboard', [BookingController::class, 'dashboard']);
    Route::get('bookings/{id}', [BookingController::class, 'show']);
    
    // Status workflows (Approve, Reject, Cancel)
    Route::patch('bookings/{id}/audit', [BookingController::class, 'audit']);

    // Payments
    Route::post('bookings/{bookingId}/payments', [BookingPaymentController::class, 'store']);
    Route::patch('bookings/{bookingId}/payments/{paymentId}/audit', [BookingPaymentController::class, 'audit']);
});
