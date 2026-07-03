<?php

use App\Modules\Branches\Presentation\Controllers\BranchController;
use App\Modules\Branches\Presentation\Controllers\BranchCoverageAreaController;
use App\Modules\Branches\Presentation\Controllers\BranchMemberController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/admin/branches')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [BranchController::class, 'index']);
    Route::post('/', [BranchController::class, 'store']);
    Route::get('/{id}', [BranchController::class, 'show']);
    Route::put('/{id}', [BranchController::class, 'update']);
    Route::patch('/{id}/status', [BranchController::class, 'changeStatus']);
    Route::get('/{id}/dashboard', [BranchController::class, 'dashboard']);

    Route::get('/{id}/coverage', [BranchCoverageAreaController::class, 'index']);
    Route::post('/{id}/coverage', [BranchCoverageAreaController::class, 'store']);
    Route::delete('/{id}/coverage/{areaId}', [BranchCoverageAreaController::class, 'destroy']);

    Route::get('/{id}/members', [BranchMemberController::class, 'index']);
    Route::post('/{id}/members', [BranchMemberController::class, 'store']);
    Route::delete('/{id}/members/{memberId}', [BranchMemberController::class, 'destroy']);
});
