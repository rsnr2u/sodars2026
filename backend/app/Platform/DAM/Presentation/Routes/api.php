<?php

declare(strict_types=1);

use App\Platform\DAM\Presentation\Controllers\AssetController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['auth:sanctum'])->group(function () {
    Route::get('dam/assets', [AssetController::class, 'index'])->name('dam.assets.index');
    Route::post('dam/assets', [AssetController::class, 'store'])->name('dam.assets.store');
    Route::get('dam/assets/{id}', [AssetController::class, 'show'])->name('dam.assets.show');
    Route::post('dam/assets/{id}/attach', [AssetController::class, 'attach'])->name('dam.assets.attach');
    Route::post('dam/assets/{id}/signed-url', [AssetController::class, 'generateSignedUrl'])->name('dam.assets.signed-url');
    
    Route::post('dam/folders', [AssetController::class, 'createFolder'])->name('dam.folders.create');
});

// Download route stub
Route::get('api/v1/dam/download-signed', function () {
    return response()->json(['message' => 'Signed download executed.']);
})->name('dam.assets.signed-download');
