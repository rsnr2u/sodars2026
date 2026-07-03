<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Branches\Domain\Entities\BranchUser;
use Database\Seeders\RolesAndPermissionsSeeder;
use Tests\Core\ApiTestCase;

class BranchMemberTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * Test assigning member user to branch.
     */
    public function test_can_assign_member(): void
    {
        $this->actingAsAdmin();

        $branch = Branch::create([
            'name' => 'Branch India North',
            'code' => 'IN-NORTH',
            'support_email' => 'north.support@sodars.com',
            'support_phone' => '+911145678901',
        ]);

        $user = User::factory()->create();

        $response = $this->postJson("/api/v1/admin/branches/{$branch->id}/members", [
            'user_id' => $user->id,
            'is_primary' => true,
        ]);

        $this->assertApiResponse($response, 201);
        $response->assertJsonPath('data.user.id', $user->id);
        $response->assertJsonPath('data.is_primary', true);
    }

    /**
     * Test user cannot be primary manager on multiple active branches.
     */
    public function test_user_cannot_be_primary_manager_of_two_branches(): void
    {
        $this->actingAsAdmin();

        $branch1 = Branch::create([
            'name' => 'Branch 1',
            'code' => 'B1',
            'support_email' => 'b1@sodars.com',
            'support_phone' => '+11111',
        ]);

        $branch2 = Branch::create([
            'name' => 'Branch 2',
            'code' => 'B2',
            'support_email' => 'b2@sodars.com',
            'support_phone' => '+22222',
        ]);

        $user = User::factory()->create();

        BranchUser::create([
            'branch_id' => $branch1->id,
            'user_id' => $user->id,
            'is_primary' => true,
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $response = $this->postJson("/api/v1/admin/branches/{$branch2->id}/members", [
            'user_id' => $user->id,
            'is_primary' => true,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('user_id');
    }
}
