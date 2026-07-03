<?php

declare(strict_types=1);

use App\Platform\Search\Presentation\Controllers\SearchController;
use App\Platform\Search\Presentation\Controllers\SavedSearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    // Search operations
    Route::get('search', [SearchController::class, 'search']);
    Route::get('search/suggest', [SearchController::class, 'suggest']);
    Route::get('search/global', [SearchController::class, 'globalSearch']);
    Route::post('search/click', [SearchController::class, 'logClick']);

    // Saved searches
    Route::get('search/saved', [SavedSearchController::class, 'index']);
    Route::post('search/saved', [SavedSearchController::class, 'store']);
    Route::delete('search/saved/{id}', [SavedSearchController::class, 'destroy']);
});
