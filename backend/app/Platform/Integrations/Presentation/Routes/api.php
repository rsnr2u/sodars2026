<?php

declare(strict_types=1);

use App\Platform\Integrations\Presentation\Controllers\ApiKeyController;
use App\Platform\Integrations\Presentation\Controllers\WebhookSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    Route::get('integrations/keys', [ApiKeyController::class, 'index']);
    Route::post('integrations/keys', [ApiKeyController::class, 'store']);
    Route::delete('integrations/keys/{id}', [ApiKeyController::class, 'destroy']);

    Route::get('integrations/webhooks', [WebhookSubscriptionController::class, 'index']);
    Route::post('integrations/webhooks', [WebhookSubscriptionController::class, 'store']);
    Route::delete('integrations/webhooks/{id}', [WebhookSubscriptionController::class, 'destroy']);
    Route::get('integrations/webhooks/{id}/logs', [WebhookSubscriptionController::class, 'logs']);
});

Route::prefix('api/public/v1')
    ->middleware([
        \App\Platform\Integrations\Infrastructure\Middleware\AuthenticateApiKey::class,
        \App\Platform\Integrations\Infrastructure\Middleware\PublicRateLimiter::class
    ])
    ->group(function () {
        Route::get('ping', function () {
            return response()->json([
                'status' => 'success',
                'message' => 'API Key authentication successful.',
                'user' => [
                    'id' => auth()->id(),
                    'name' => auth()->user()?->name,
                ]
            ]);
        });
    });
