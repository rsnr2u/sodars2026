<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Core\FeatureTestCase;

class HealthApiTest extends FeatureTestCase
{
    /**
     * Test the legacy v1 API health endpoint reports correctly.
     */
    public function test_health_check_endpoint(): void
    {
        $response = $this->getJson('/api/v1/health');

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
}
