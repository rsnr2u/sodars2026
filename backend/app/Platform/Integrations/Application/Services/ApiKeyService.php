<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Application\Services;

use App\Platform\Integrations\Domain\ApiKeys\ApiKey;
use Illuminate\Support\Str;

class ApiKeyService
{
    /**
     * Create developer API Key.
     * Returns an array containing the ApiKey model and the raw plain text key.
     *
     * @return array{apiKey: ApiKey, plainTextKey: string}
     */
    public function createKey(string $userId, string $name, array $scopes = [], bool $isTest = false): array
    {
        $environment = $isTest ? 'test' : 'live';
        $prefix = "sodars_{$environment}_";
        
        $secret = Str::random(32);
        $plainTextKey = $prefix . $secret;

        $hash = hash('sha256', $secret);

        $apiKey = ApiKey::create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'name' => $name,
            'key_prefix' => $prefix,
            'secret_hash' => $hash,
            'scopes' => $scopes,
            'is_active' => true,
        ]);

        return [
            'apiKey' => $apiKey,
            'plainTextKey' => $plainTextKey,
        ];
    }

    /**
     * Revoke developer API Key.
     */
    public function revokeKey(string $id, string $userId): void
    {
        ApiKey::where('id', $id)
            ->where('user_id', $userId)
            ->update([
                'revoked_at' => now(),
                'is_active' => false,
            ]);
    }
}
