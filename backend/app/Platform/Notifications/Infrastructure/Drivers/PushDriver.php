<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Infrastructure\Drivers;

use App\Platform\Notifications\Domain\Contracts\ChannelDriver;

class PushDriver implements ChannelDriver
{
    public function send(string $recipient, string $body, ?string $subject = null, array $options = []): array
    {
        return [
            'success' => true,
            'provider_reference' => 'push-' . uniqid(),
            'provider_response' => 'FCM Token push delivered.',
            'provider_status_code' => 200,
            'error_message' => null,
        ];
    }

    public function getCapabilities(): array
    {
        return [
            'supports_subject' => true,
            'supports_attachment' => false,
            'supports_html' => false,
            'supports_template' => false,
            'supports_buttons' => true,
        ];
    }
}
