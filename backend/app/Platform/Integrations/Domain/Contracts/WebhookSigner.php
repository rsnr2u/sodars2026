<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Domain\Contracts;

interface WebhookSigner
{
    /**
     * Compute cryptographical signature of request payload with secret token.
     */
    public function sign(string $payload, string $secret, int $timestamp): string;
}
