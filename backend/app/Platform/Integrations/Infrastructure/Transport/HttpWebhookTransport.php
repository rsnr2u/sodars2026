<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Infrastructure\Transport;

use App\Platform\Integrations\Domain\Contracts\WebhookTransport;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

class HttpWebhookTransport implements WebhookTransport
{
    protected Client $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client([
            'timeout' => 10.0,
            'connect_timeout' => 5.0,
        ]);
    }

    public function send(string $url, string $payload, array $headers): array
    {
        try {
            $response = $this->client->post($url, [
                'headers' => array_merge($headers, [
                    'Content-Type' => 'application/json',
                ]),
                'body' => $payload,
                'http_errors' => false,
            ]);

            return [
                'status' => $response->getStatusCode(),
                'body' => (string) $response->getBody(),
                'headers' => $response->getHeaders(),
                'error' => null,
            ];
        } catch (TransferException $e) {
            return [
                'status' => 0,
                'body' => '',
                'headers' => [],
                'error' => $e->getMessage(),
            ];
        }
    }
}
