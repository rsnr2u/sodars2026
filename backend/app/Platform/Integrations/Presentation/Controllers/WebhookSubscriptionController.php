<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Platform\Integrations\Domain\Webhooks\WebhookSubscription;
use App\Platform\Integrations\Domain\Webhooks\WebhookDeliveryLog;
use App\Platform\Integrations\Application\Services\WebhookSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookSubscriptionController extends BaseApiController
{
    public function __construct(
        protected WebhookSubscriptionService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $subscriptions = WebhookSubscription::where('user_id', $userId)->get();

        return $this->successResponse($subscriptions, 'Webhook Subscriptions list.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'target_url' => 'required|url',
            'event_types' => 'required|array',
            'event_types.*' => 'required|string',
        ]);

        $userId = (string) $request->user()->id;

        try {
            $sub = $this->service->subscribe(
                $userId,
                $request->input('target_url'),
                $request->input('event_types')
            );

            return $this->successResponse($sub, 'Webhook Subscription created successfully.', 201);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    public function destroy(string $id, Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $this->service->unsubscribe($id, $userId);

        return $this->successResponse(null, 'Webhook Subscription deactivated.');
    }

    public function logs(string $id, Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $sub = WebhookSubscription::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $logs = WebhookDeliveryLog::where('webhook_subscription_id', $sub->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return $this->successResponse($logs, 'Webhook delivery logs retrieved.');
    }
}
