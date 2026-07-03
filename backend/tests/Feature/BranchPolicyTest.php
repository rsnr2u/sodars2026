<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Branches\Domain\Entities\BranchUser;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\Sanctum;
use Tests\Core\ApiTestCase;

class BranchPolicyTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * Test manager can access their own branch profile.
     */
    public function test_branch_manager_can_view_own_branch(): void
    {
        $branch = Branch::create([
            'name' => 'Branch Delhi',
            'code' => 'DELHI',
            'support_email' => 'delhi@sodars.com',
            'support_phone' => '+11111',
        ]);

        $manager = User::factory()->create();
        $manager->assignRole('branch_manager');

        BranchUser::create([
            'branch_id' => $branch->id,
            'user_id' => $manager->id,
            'is_primary' => true,
            'is_active' => true,
            'joined_at' => now(),
        ]);

        Sanctum::actingAs($manager, ['*']);

        $response = $this->getJson("/api/v1/admin/branches/{$branch->id}");

        $this->assertApiResponse($response, 200);
    }

    /**
     * Test manager is denied access to other branch profiles.
     */
    public function test_branch_manager_cannot_view_other_branch(): void
    {
        $branch1 = Branch::create([
            'name' => 'Branch Delhi',
            'code' => 'DELHI',
            'support_email' => 'delhi@sodars.com',
            'support_phone' => '+11111',
        ]);

        $branch2 = Branch::create([
            'name' => 'Branch Mumbai',
            'code' => 'MUMBAI',
            'support_email' => 'mumbai@sodars.com',
            'support_phone' => '+22222',
        ]);

        $manager = User::factory()->create();
        $manager->assignRole('branch_manager');

        BranchUser::create([
            'branch_id' => $branch1->id,
            'user_id' => $manager->id,
            'is_primary' => true,
            'is_active' => true,
            'joined_at' => now(),
        ]);

        Sanctum::actingAs($manager, ['*']);

        $response = $this->getJson("/api/v1/admin/branches/{$branch2->id}");

        $response->assertStatus(403);
    }
}
