<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Application\Services;

use App\Platform\Integrations\Domain\Webhooks\WebhookSubscription;
use App\Platform\Integrations\Infrastructure\Registry\WebhookEventRegistry;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WebhookSubscriptionService
{
    /**
     * Create developer Webhook Subscription.
     */
    public function subscribe(string $userId, string $targetUrl, array $eventTypes): WebhookSubscription
    {
        foreach ($eventTypes as $event) {
            if (!WebhookEventRegistry::isValid($event)) {
                throw new InvalidArgumentException("Webhook event '{$event}' is not supported by the system.");
            }
        }

        return WebhookSubscription::create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'target_url' => $targetUrl,
            'secret_token' => 'whsec_' . Str::random(24),
            'event_types' => $eventTypes,
            'is_active' => true,
        ]);
    }

    /**
     * Deactivate subscription.
     */
    public function unsubscribe(string $id, string $userId): void
    {
        WebhookSubscription::where('id', $id)
            ->where('user_id', $userId)
            ->update(['is_active' => false]);
    }
}
