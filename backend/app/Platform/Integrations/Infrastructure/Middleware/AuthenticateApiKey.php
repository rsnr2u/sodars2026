<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Infrastructure\Middleware;

use App\Platform\Integrations\Domain\ApiKeys\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('X-API-KEY');
        if (is_array($header)) {
            $header = reset($header);
        }

        $apiKeyString = (string) $header;
        if (empty($apiKeyString)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated: X-API-KEY header missing.',
            ], 401);
        }

        $parts = explode('_', $apiKeyString);
        if (count($parts) < 3) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated: Invalid API key format.',
            ], 401);
        }

        $prefix = $parts[0] . '_' . $parts[1] . '_';
        $secret = $parts[2];

        $hash = hash('sha256', $secret);

        $apiKey = ApiKey::where('key_prefix', $prefix)
            ->where('secret_hash', $hash)
            ->where('is_active', true)
            ->whereNull('revoked_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$apiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated: Invalid or revoked API key.',
            ], 401);
        }

        $apiKey->update([
            'last_used_at' => now(),
            'last_ip' => $request->ip(),
            'last_user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        $request->attributes->set('authenticated_api_key', $apiKey);

        Auth::login($apiKey->user);

        return $next($request);
    }
}
