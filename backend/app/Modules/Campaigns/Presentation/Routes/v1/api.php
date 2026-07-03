<?php

declare(strict_types=1);

use App\Modules\Campaigns\Presentation\Controllers\CampaignController;
use App\Modules\Campaigns\Presentation\Controllers\CampaignCreativeController;
use App\Modules\Campaigns\Presentation\Controllers\CampaignProofController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    Route::get('campaigns', [CampaignController::class, 'index']);
    Route::post('campaigns', [CampaignController::class, 'store']);
    Route::get('campaigns/dashboard', [CampaignController::class, 'dashboard']);
    Route::get('campaigns/{id}', [CampaignController::class, 'show']);
    Route::put('campaigns/{id}', [CampaignController::class, 'update']);
    Route::patch('campaigns/{id}/status', [CampaignController::class, 'updateStatus']);

    // Creatives
    Route::post('campaigns/{campaignId}/creatives', [CampaignCreativeController::class, 'store']);
    Route::patch('campaigns/{campaignId}/creatives/{creativeId}/audit', [CampaignCreativeController::class, 'audit']);

    // Proof of performance
    Route::post('campaigns/{campaignId}/proofs', [CampaignProofController::class, 'store']);
    Route::patch('campaigns/{campaignId}/proofs/{proofId}/audit', [CampaignProofController::class, 'audit']);
});
