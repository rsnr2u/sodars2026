<?php

declare(strict_types=1);

use App\Modules\Inventory\Presentation\Controllers\InventoryAvailabilityController;
use App\Modules\Inventory\Presentation\Controllers\InventoryController;
use App\Modules\Inventory\Presentation\Controllers\InventoryFaceController;
use App\Modules\Inventory\Presentation\Controllers\InventoryPricingController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    // Inventory CRUD
    Route::get('inventories', [InventoryController::class, 'index']);
    Route::post('inventories', [InventoryController::class, 'store']);
    Route::get('inventories/search', [InventoryController::class, 'search']);
    Route::get('inventories/nearby', [InventoryController::class, 'nearby']);
    Route::get('inventories/dashboard', [InventoryController::class, 'dashboard']);
    Route::get('inventories/{id}', [InventoryController::class, 'show']);
    Route::put('inventories/{id}', [InventoryController::class, 'update']);
    Route::patch('inventories/{id}/status', [InventoryController::class, 'updateStatus']);

    // Documents
    Route::post('inventories/{id}/documents', [InventoryController::class, 'uploadDocument']);

    // Faces
    Route::post('inventories/{inventoryId}/faces', [InventoryFaceController::class, 'store']);

    // Pricing on faces
    Route::post('inventories/{inventoryId}/faces/{faceId}/pricing', [InventoryPricingController::class, 'store']);

    // Availability on faces
    Route::get('inventories/{inventoryId}/faces/{faceId}/availability', [InventoryAvailabilityController::class, 'index']);
    Route::post('inventories/{inventoryId}/faces/{faceId}/availability', [InventoryAvailabilityController::class, 'store']);
    Route::delete('inventories/{inventoryId}/faces/{faceId}/availability/{availabilityId}', [InventoryAvailabilityController::class, 'destroy']);
});
