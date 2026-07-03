<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Domain\Contracts;

interface ChannelDriver
{
    /**
     * Send notification payload.
     * Returns an array tracking delivery details:
     * [
     *     'success' => bool,
     *     'provider_reference' => ?string,
     *     'provider_response' => ?string,
     *     'provider_status_code' => ?int,
     *     'error_message' => ?string
     * ]
     */
    public function send(string $recipient, string $body, ?string $subject = null, array $options = []): array;

    /**
     * Get features metadata support.
     */
    public function getCapabilities(): array;
}
