<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Infrastructure\Signers;

use App\Platform\Integrations\Domain\Contracts\WebhookSigner;

class HmacWebhookSigner implements WebhookSigner
{
    public function sign(string $payload, string $secret, int $timestamp): string
    {
        $signingPayload = "t={$timestamp}." . $payload;
        return hash_hmac('sha256', $signingPayload, $secret);
    }
}
