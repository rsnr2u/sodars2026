<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class PublicRateLimiter
{
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        $apiKey = $request->attributes->get('authenticated_api_key');
        
        $throttleKey = $apiKey ? 'api_key:' . $apiKey->key_prefix : 'ip:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'status' => 'error',
                'message' => 'Too Many Requests. Rate limit exceeded.',
                'retry_after_seconds' => $seconds,
            ], 429, [
                'Retry-After' => $seconds,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        RateLimiter::hit($throttleKey, $decayMinutes * 60);

        $response = $next($request);

        $remaining = RateLimiter::remaining($throttleKey, $maxAttempts);
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remaining,
        ]);

        return $response;
    }
}
