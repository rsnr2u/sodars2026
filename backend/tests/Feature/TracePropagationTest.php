<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Context\TraceContext;
use Tests\Core\FeatureTestCase;

class TracePropagationTest extends FeatureTestCase
{
    /**
     * Test that correlation ID is generated when not provided.
     */
    public function test_correlation_id_generated_when_absent(): void
    {
        $response = $this->getJson('/api/health/live');

        $response->assertStatus(200);
        $this->assertNotEmpty($response->headers->get('X-Correlation-ID'));
        $this->assertNotEmpty($response->headers->get('X-Trace-ID'));
    }

    /**
     * Test that client-provided correlation ID is propagated.
     */
    public function test_correlation_id_propagated_from_request(): void
    {
        $clientCorrelationId = 'test-correlation-' . uniqid();

        $response = $this->getJson('/api/health/live', [
            'X-Correlation-ID' => $clientCorrelationId,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($clientCorrelationId, $response->headers->get('X-Correlation-ID'));
    }

    /**
     * Test that client-provided trace ID is propagated.
     */
    public function test_trace_id_propagated_from_request(): void
    {
        $clientTraceId = 'test-trace-' . uniqid();

        $response = $this->getJson('/api/health/live', [
            'X-Trace-ID' => $clientTraceId,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($clientTraceId, $response->headers->get('X-Trace-ID'));
    }

    /**
     * Test that causation ID is propagated when provided.
     */
    public function test_causation_id_propagated_from_request(): void
    {
        $clientCausationId = 'test-causation-' . uniqid();

        $response = $this->getJson('/api/health/live', [
            'X-Causation-ID' => $clientCausationId,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($clientCausationId, $response->headers->get('X-Causation-ID'));
    }

    /**
     * Test that TraceContext singleton contains the correct IDs after middleware.
     */
    public function test_trace_context_contains_correct_ids(): void
    {
        $correlationId = 'ctx-correlation-' . uniqid();
        $traceId = 'ctx-trace-' . uniqid();

        $this->getJson('/api/health/live', [
            'X-Correlation-ID' => $correlationId,
            'X-Trace-ID' => $traceId,
        ]);

        $context = app(TraceContext::class);
        $this->assertEquals($correlationId, $context->getCorrelationId());
        $this->assertEquals($traceId, $context->getTraceId());
    }

    /**
     * Test that each request gets unique trace IDs when not provided.
     */
    public function test_each_request_gets_unique_ids(): void
    {
        $response1 = $this->getJson('/api/health/live');
        $correlation1 = $response1->headers->get('X-Correlation-ID');

        // Force the container to re-resolve TraceContext for the next request
        $this->app->forgetInstance(TraceContext::class);

        $response2 = $this->getJson('/api/health/live');
        $correlation2 = $response2->headers->get('X-Correlation-ID');

        $this->assertNotEquals($correlation1, $correlation2);
    }
}
