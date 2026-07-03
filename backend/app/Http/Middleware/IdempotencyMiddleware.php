<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Contracts\LockServiceInterface;
use App\Core\Context\TraceContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class IdempotencyMiddleware
{
    protected LockServiceInterface $lockService;

    public function __construct(LockServiceInterface $lockService)
    {
        $this->lockService = $lockService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Only enforce on write methods
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        $key = $request->header('Idempotency-Key');
        if (is_array($key)) {
            $key = reset($key);
        }

        if (!$key || !is_string($key)) {
            return $next($request);
        }

        $userId = Auth::id();
        $requestHash = sha1($request->method() . '|' . $request->fullUrl() . '|' . $request->getContent());

        // 2. Lookup existing record
        $record = DB::table('idempotency_keys')
            ->where('key', $key)
            ->first();

        if ($record) {
            // Check request payload hash matches to ensure it's not a different request with the same key
            if ($record->request_hash !== $requestHash) {
                return response()->json([
                    'success' => false,
                    'message' => 'Idempotency key mismatch with request payload.',
                    'errors' => ['key' => 'Key has already been used with different parameters.'],
                    'meta' => [
                        'correlation_id' => TraceContext::correlationId(),
                    ],
                ], 422);
            }

            // If processing is completed, return the stored response
            if ($record->processing_finished_at !== null && $record->response_status !== null) {
                $headers = json_decode($record->response_headers, true) ?? [];
                $headers['X-Cache-Lookup'] = 'HIT - Idempotency Replay';

                $body = json_decode($record->response_body, true) ?? [];

                return response()->json($body, $record->response_status, $headers);
            }

            // If it is currently executing in another thread
            return response()->json([
                'success' => false,
                'message' => 'Concurrent request in progress.',
                'errors' => [],
                'meta' => [
                    'correlation_id' => TraceContext::correlationId(),
                ],
            ], 409);
        }

        // 3. Acquire distributed lock to avoid concurrent race condition insert
        $lockKey = "idempotency:{$key}";
        if (!$this->lockService->acquire($lockKey, 60)) {
            return response()->json([
                'success' => false,
                'message' => 'Concurrent request execution lock failed.',
                'errors' => [],
                'meta' => [
                    'correlation_id' => TraceContext::correlationId(),
                ],
            ], 409);
        }

        $id = (string) Str::uuid();
        $ttlHours = (int) config('foundation.idempotency.ttl_hours', 24);

        // Record start in database
        DB::table('idempotency_keys')->insert([
            'id' => $id,
            'key' => $key,
            'user_id' => $userId,
            'request_hash' => $requestHash,
            'request_method' => $request->method(),
            'request_uri' => $request->getRequestUri(),
            'request_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'processing_started_at' => now(),
            'last_seen_at' => now(),
            'expires_at' => now()->addHours($ttlHours),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $response = $next($request);

            // Persist the response content
            $body = $response->getContent();
            $bodyJson = json_decode($body ?: '{}', true);

            $headers = [];
            foreach ($response->headers->all() as $name => $values) {
                $headers[$name] = reset($values);
            }

            DB::table('idempotency_keys')
                ->where('id', $id)
                ->update([
                    'response_status' => $response->getStatusCode(),
                    'response_headers' => json_encode($headers, JSON_THROW_ON_ERROR),
                    'response_body' => json_encode($bodyJson, JSON_THROW_ON_ERROR),
                    'processing_finished_at' => now(),
                    'updated_at' => now(),
                ]);

            return $response;

        } catch (Throwable $e) {
            // Delete the record so the client can try again
            DB::table('idempotency_keys')->where('id', $id)->delete();
            throw $e;
        } finally {
            $this->lockService->release($lockKey);
        }
    }
}
