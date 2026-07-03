<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Branches\Domain\Entities\Branch;
use Database\Seeders\RolesAndPermissionsSeeder;
use Tests\Core\ApiTestCase;

class BranchStatusTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * Test valid branch status transitions.
     */
    public function test_can_transition_branch_status(): void
    {
        $this->actingAsAdmin();

        $branch = Branch::create([
            'name' => 'Branch India North',
            'code' => 'IN-NORTH',
            'support_email' => 'north.support@sodars.com',
            'support_phone' => '+911145678901',
            'status' => 'active',
        ]);

        $response = $this->patchJson("/api/v1/admin/branches/{$branch->id}/status", [
            'status' => 'inactive',
        ]);

        $this->assertApiResponse($response, 200);
        $response->assertJsonPath('data.status', 'inactive');
    }

    /**
     * Test invalid transitions are blocked.
     */
    public function test_invalid_status_transition_fails(): void
    {
        $this->actingAsAdmin();

        $branch = Branch::create([
            'name' => 'Branch India North',
            'code' => 'IN-NORTH',
            'support_email' => 'north.support@sodars.com',
            'support_phone' => '+911145678901',
            'status' => 'active',
        ]);

        $response = $this->patchJson("/api/v1/admin/branches/{$branch->id}/status", [
            'status' => 'archived', // Direct active -> archived transition is forbidden
        ]);

        $response->assertStatus(422);
    }
}
