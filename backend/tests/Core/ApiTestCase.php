<?php

declare(strict_types=1);

namespace Tests\Core;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\TestResponse;

abstract class ApiTestCase extends FeatureTestCase
{
    /**
     * Authenticate a test user via Sanctum.
     */
    protected function actingAsUser(?User $user = null): User
    {
        /** @var User $createdUser */
        $createdUser = $user ?? User::factory()->create();
        Sanctum::actingAs($createdUser, ['*']);

        return $createdUser;
    }

    /**
     * Authenticate a test user as Super Admin.
     */
    protected function actingAsAdmin(): User
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        Sanctum::actingAs($admin, ['*']);

        return $admin;
    }

    /**
     * Assert the response matches the unified API layout.
     */
    protected function assertApiResponse(TestResponse $response, int $expectedStatus = 200): void
    {
        $response->assertStatus($expectedStatus)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'errors',
                'meta',
            ]);
    }
}
