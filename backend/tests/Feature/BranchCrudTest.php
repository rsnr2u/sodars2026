<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Branches\Domain\Entities\Branch;
use Database\Seeders\RolesAndPermissionsSeeder;
use Tests\Core\ApiTestCase;

class BranchCrudTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * Test Super Admin can create a new branch.
     */
    public function test_super_admin_can_create_branch(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/v1/admin/branches', [
            'name' => 'Branch India North',
            'code' => 'IN-NORTH',
            'timezone' => 'Asia/Kolkata',
            'currency_code' => 'INR',
            'markup_percentage' => 15,
            'support_email' => 'north.support@sodars.com',
            'support_phone' => '+911145678901',
        ]);

        $this->assertApiResponse($response, 201);
        $response->assertJsonPath('data.name', 'Branch India North');
        $response->assertJsonPath('data.code', 'IN-NORTH');
        $response->assertJsonPath('data.status', 'active');
    }

    /**
     * Test markup validation constraints.
     */
    public function test_markup_percentage_validation(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/v1/admin/branches', [
            'name' => 'Branch India North',
            'code' => 'IN-NORTH',
            'timezone' => 'Asia/Kolkata',
            'currency_code' => 'INR',
            'markup_percentage' => 25, // Invalid: max limit is 20
            'support_email' => 'north.support@sodars.com',
            'support_phone' => '+911145678901',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('markup_percentage');
    }

    /**
     * Test unique constraints on name and code fields.
     */
    public function test_unique_name_and_code(): void
    {
        $this->actingAsAdmin();

        Branch::create([
            'name' => 'Branch India North',
            'code' => 'IN-NORTH',
            'support_email' => 'north.support@sodars.com',
            'support_phone' => '+911145678901',
        ]);

        $response = $this->postJson('/api/v1/admin/branches', [
            'name' => 'Branch India North',
            'code' => 'IN-NORTH',
            'support_email' => 'north.support2@sodars.com',
            'support_phone' => '+911145678902',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'code']);
    }

    /**
     * Test listing branches.
     */
    public function test_can_list_branches(): void
    {
        $this->actingAsAdmin();

        Branch::create([
            'name' => 'Branch North',
            'code' => 'NORTH',
            'support_email' => 'north@sodars.com',
            'support_phone' => '+1111111111',
        ]);

        $response = $this->getJson('/api/v1/admin/branches');

        $this->assertApiResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => ['id', 'name', 'code', 'status'],
                ],
            ],
        ]);
    }
}
