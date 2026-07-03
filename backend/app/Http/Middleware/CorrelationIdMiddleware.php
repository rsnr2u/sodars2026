<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Context\TraceContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CorrelationIdMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Resolve or generate Correlation ID
        $correlationId = $request->header('X-Correlation-ID');
        if (is_array($correlationId)) {
            $correlationId = reset($correlationId);
        }
        if (!$correlationId || !is_string($correlationId)) {
            $correlationId = (string) Str::uuid();
        }

        // 2. Resolve or generate Trace ID
        $traceId = $request->header('X-Trace-ID');
        if (is_array($traceId)) {
            $traceId = reset($traceId);
        }
        if (!$traceId || !is_string($traceId)) {
            $traceId = (string) Str::uuid();
        }

        // 3. Resolve or generate Causation ID
        $causationId = $request->header('X-Causation-ID');
        if (is_array($causationId)) {
            $causationId = reset($causationId);
        }
        if (!$causationId || !is_string($causationId)) {
            $causationId = null;
        }

        // Bind to request-scoped trace context singleton
        $context = app(TraceContext::class);
        $context->setCorrelationId($correlationId);
        $context->setTraceId($traceId);
        $context->setCausationId($causationId);

        // Continue execution
        $response = $next($request);

        // Stamp headers on response
        $response->headers->set('X-Correlation-ID', $correlationId);
        $response->headers->set('X-Trace-ID', $traceId);
        if ($causationId) {
            $response->headers->set('X-Causation-ID', $causationId);
        }

        return $response;
    }
}
