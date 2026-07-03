<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Infrastructure\Drivers;

use App\Platform\Notifications\Domain\Contracts\ChannelDriver;

class SmsDriver implements ChannelDriver
{
    public function send(string $recipient, string $body, ?string $subject = null, array $options = []): array
    {
        // Mock SMS provider endpoint response
        return [
            'success' => true,
            'provider_reference' => 'sms-' . uniqid(),
            'provider_response' => 'SMS mock text sent successfully.',
            'provider_status_code' => 200,
            'error_message' => null,
        ];
    }

    public function getCapabilities(): array
    {
        return [
            'supports_subject' => false,
            'supports_attachment' => false,
            'supports_html' => false,
            'supports_template' => false,
            'supports_buttons' => false,
        ];
    }
}
