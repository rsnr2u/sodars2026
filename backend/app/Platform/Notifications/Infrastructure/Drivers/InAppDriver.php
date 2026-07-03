<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Infrastructure\Drivers;

use App\Platform\Notifications\Domain\Contracts\ChannelDriver;
use App\Platform\Notifications\Domain\Entities\InAppNotification;
use Illuminate\Support\Str;

class InAppDriver implements ChannelDriver
{
    public function send(string $recipient, string $body, ?string $subject = null, array $options = []): array
    {
        try {
            // Recipient corresponds to user UUID string
            InAppNotification::create([
                'id' => (string) Str::uuid(),
                'user_id' => $recipient,
                'dispatch_id' => $options['dispatch_id'] ?? null,
                'title' => $subject ?? 'System Alert',
                'message' => $body,
                'type' => $options['type'] ?? 'info',
                'link_url' => $options['link_url'] ?? null,
                'is_read' => false,
            ]);

            return [
                'success' => true,
                'provider_reference' => 'inapp-' . uniqid(),
                'provider_response' => 'In-app notification saved successfully to database.',
                'provider_status_code' => 200,
                'error_message' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'provider_reference' => null,
                'provider_response' => null,
                'provider_status_code' => 500,
                'error_message' => $e->getMessage(),
            ];
        }
    }

    public function getCapabilities(): array
    {
        return [
            'supports_subject' => true,
            'supports_attachment' => false,
            'supports_html' => false,
            'supports_template' => false,
            'supports_buttons' => false,
        ];
    }
}
