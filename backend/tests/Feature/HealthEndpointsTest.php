<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Core\FeatureTestCase;

class HealthEndpointsTest extends FeatureTestCase
{
    /**
     * Test the /health/live endpoint returns 200 with UP status.
     */
    public function test_live_endpoint_returns_up(): void
    {
        $response = $this->getJson('/api/health/live');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'UP')
            ->assertJsonStructure([
                'status',
                'timestamp',
            ]);
    }

    /**
     * Test the /health/ready endpoint returns structured checks.
     */
    public function test_ready_endpoint_returns_structured_checks(): void
    {
        $response = $this->getJson('/api/health/ready');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'UP')
            ->assertJsonStructure([
                'status',
                'timestamp',
                'checks' => [
                    'database' => ['healthy', 'message'],
                    'cache' => ['healthy', 'message'],
                    'storage' => ['healthy', 'message'],
                ],
            ]);
    }

    /**
     * Test the /health/details endpoint is restricted when APP_DEBUG=false.
     */
    public function test_details_endpoint_requires_authorization(): void
    {
        // Force APP_DEBUG=false and details_requires_auth=true
        config([
            'app.debug' => false,
            'foundation.health.details_requires_auth' => true,
        ]);

        $response = $this->getJson('/api/health/details');

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    /**
     * Test the /health/details endpoint is accessible when APP_DEBUG=true.
     */
    public function test_details_endpoint_accessible_in_debug(): void
    {
        config(['app.debug' => true]);

        $response = $this->getJson('/api/health/details');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'UP')
            ->assertJsonStructure([
                'status',
                'timestamp',
                'environment',
                'version' => ['app', 'laravel', 'php'],
                'git' => ['hash', 'branch'],
                'checks' => [
                    'database' => ['healthy', 'message', 'latency_ms', 'driver'],
                    'cache' => ['healthy', 'message', 'latency_ms', 'driver'],
                    'storage' => ['healthy', 'message', 'latency_ms', 'driver'],
                    'mailer' => ['healthy', 'message'],
                ],
            ]);
    }

    /**
     * Test the /health/details endpoint returns latency values as numbers.
     */
    public function test_details_endpoint_returns_numeric_latencies(): void
    {
        config(['app.debug' => true]);

        $response = $this->getJson('/api/health/details');

        $data = $response->json();

        $this->assertIsNumeric($data['checks']['database']['latency_ms']);
        $this->assertIsNumeric($data['checks']['cache']['latency_ms']);
        $this->assertIsNumeric($data['checks']['storage']['latency_ms']);
    }
}
