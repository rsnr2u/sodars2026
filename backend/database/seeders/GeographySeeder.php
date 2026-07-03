<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Platform\Shared\Domain\Entities\City;
use App\Platform\Shared\Domain\Entities\Country;
use App\Platform\Shared\Domain\Entities\District;
use App\Platform\Shared\Domain\Entities\Pincode;
use App\Platform\Shared\Domain\Entities\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GeographySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. India setup
        $india = Country::create([
            'id' => (string) Str::uuid(),
            'name' => 'India',
            'iso_code' => 'IND',
        ]);

        $ap = State::create([
            'id' => (string) Str::uuid(),
            'country_id' => $india->id,
            'name' => 'Andhra Pradesh',
        ]);

        $gunturDistrict = District::create([
            'id' => (string) Str::uuid(),
            'state_id' => $ap->id,
            'name' => 'Guntur',
        ]);

        $gunturCity = City::create([
            'id' => (string) Str::uuid(),
            'district_id' => $gunturDistrict->id,
            'name' => 'Guntur',
        ]);

        Pincode::create([
            'id' => (string) Str::uuid(),
            'city_id' => $gunturCity->id,
            'code' => '522001',
        ]);

        // 2. USA setup
        $usa = Country::create([
            'id' => (string) Str::uuid(),
            'name' => 'United States',
            'iso_code' => 'USA',
        ]);

        $ca = State::create([
            'id' => (string) Str::uuid(),
            'country_id' => $usa->id,
            'name' => 'California',
        ]);

        $laDistrict = District::create([
            'id' => (string) Str::uuid(),
            'state_id' => $ca->id,
            'name' => 'Los Angeles County',
        ]);

        $laCity = City::create([
            'id' => (string) Str::uuid(),
            'district_id' => $laDistrict->id,
            'name' => 'Los Angeles',
        ]);

        Pincode::create([
            'id' => (string) Str::uuid(),
            'city_id' => $laCity->id,
            'code' => '90001',
        ]);
    }
}
