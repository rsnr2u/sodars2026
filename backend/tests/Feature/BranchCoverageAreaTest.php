<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Branches\Domain\Entities\Branch;
use App\Platform\Shared\Domain\Entities\City;
use Database\Seeders\GeographySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Tests\Core\ApiTestCase;

class BranchCoverageAreaTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RolesAndPermissionsSeeder::class,
            GeographySeeder::class,
        ]);
    }

    /**
     * Test adding city coverage bounds.
     */
    public function test_can_add_coverage_area(): void
    {
        $this->actingAsAdmin();

        $branch = Branch::create([
            'name' => 'Branch India North',
            'code' => 'IN-NORTH',
            'support_email' => 'north.support@sodars.com',
            'support_phone' => '+911145678901',
        ]);

        $city = City::where('name', 'Guntur')->firstOrFail();
        $district = $city->district;
        $state = $district->state;
        $country = $state->country;

        $response = $this->postJson("/api/v1/admin/branches/{$branch->id}/coverage", [
            'country_id' => $country->id,
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
        ]);

        $this->assertApiResponse($response, 201);
        $response->assertJsonPath('data.city.id', $city->id);
    }

    /**
     * Test duplicate coverage prevention.
     */
    public function test_prevent_duplicate_coverage(): void
    {
        $this->actingAsAdmin();

        $branch = Branch::create([
            'name' => 'Branch India North',
            'code' => 'IN-NORTH',
            'support_email' => 'north.support@sodars.com',
            'support_phone' => '+911145678901',
        ]);

        $city = City::where('name', 'Guntur')->firstOrFail();
        $district = $city->district;
        $state = $district->state;
        $country = $state->country;

        $this->postJson("/api/v1/admin/branches/{$branch->id}/coverage", [
            'country_id' => $country->id,
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
        ]);

        $response = $this->postJson("/api/v1/admin/branches/{$branch->id}/coverage", [
            'country_id' => $country->id,
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('city_id');
    }
}
