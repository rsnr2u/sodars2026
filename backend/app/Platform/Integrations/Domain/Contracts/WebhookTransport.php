<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Domain\Contracts;

interface WebhookTransport
{
    /**
     * Send payload to target URL with defined headers.
     *
     * @return array{status: int, body: string, headers: array, error: ?string}
     */
    public function send(string $url, string $payload, array $headers): array;
}
