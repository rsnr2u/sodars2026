<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Infrastructure\Drivers;

use App\Platform\Notifications\Domain\Contracts\ChannelDriver;

class WhatsAppDriver implements ChannelDriver
{
    public function send(string $recipient, string $body, ?string $subject = null, array $options = []): array
    {
        return [
            'success' => true,
            'provider_reference' => 'wa-' . uniqid(),
            'provider_response' => 'WhatsApp message template delivered.',
            'provider_status_code' => 200,
            'error_message' => null,
        ];
    }

    public function getCapabilities(): array
    {
        return [
            'supports_subject' => false,
            'supports_attachment' => true,
            'supports_html' => false,
            'supports_template' => true,
            'supports_buttons' => true,
        ];
    }
}
