<?php

declare(strict_types=1);

namespace App\Modules\Branches\Infrastructure\Database\Seeders;

use App\Models\User;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Branches\Domain\Entities\BranchCoverageArea;
use App\Modules\Branches\Domain\Entities\BranchUser;
use App\Platform\Shared\Domain\Entities\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class BranchSeeder extends Seeder
{
    /**
     * Seed initial branches, members and coverage.
     */
    public function run(): void
    {
        // Ensure branch_manager role exists in Spatie
        \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'branch_manager', 'guard_name' => 'web'],
            ['id' => (string) \Illuminate\Support\Str::uuid()]
        );
        \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'branch_staff', 'guard_name' => 'web'],
            ['id' => (string) \Illuminate\Support\Str::uuid()]
        );

        // 1. Create Delhi Manager User
        $manager1 = User::updateOrCreate(
            ['email' => 'manager.delhi@sodars.com'],
            [
                'name' => 'Delhi Branch Manager',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $manager1->assignRole('branch_manager');

        // Create Mumbai Manager User
        $manager2 = User::updateOrCreate(
            ['email' => 'manager.mumbai@sodars.com'],
            [
                'name' => 'Mumbai Branch Manager',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $manager2->assignRole('branch_manager');

        // 2. Create Delhi/North Branch
        $branch1 = Branch::updateOrCreate(
            ['code' => 'DELHI-01'],
            [
                'name' => 'Branch India North',
                'timezone' => 'Asia/Kolkata',
                'currency_code' => 'INR',
                'markup_percentage' => 15,
                'support_email' => 'north.support@sodars.com',
                'support_phone' => '+911145678901',
                'status' => 'active',
            ]
        );

        // Assign manager to Delhi branch
        BranchUser::updateOrCreate(
            ['branch_id' => $branch1->id, 'user_id' => $manager1->id],
            [
                'is_primary' => true,
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        // 3. Create Mumbai/West Branch
        $branch2 = Branch::updateOrCreate(
            ['code' => 'MUMBAI-01'],
            [
                'name' => 'Branch India West',
                'timezone' => 'Asia/Kolkata',
                'currency_code' => 'INR',
                'markup_percentage' => 18,
                'support_email' => 'west.support@sodars.com',
                'support_phone' => '+912245678902',
                'status' => 'active',
            ]
        );

        // Assign manager to Mumbai branch
        BranchUser::updateOrCreate(
            ['branch_id' => $branch2->id, 'user_id' => $manager2->id],
            [
                'is_primary' => true,
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        // 4. Map Guntur city bounds
        $gunturCity = City::where('name', 'Guntur')->first();
        if ($gunturCity) {
            $gunturDistrict = $gunturCity->district;
            $ap = $gunturDistrict->state;
            $india = $ap->country;

            BranchCoverageArea::updateOrCreate(
                ['branch_id' => $branch1->id, 'city_id' => $gunturCity->id],
                [
                    'country_id' => $india->id,
                    'state_id' => $ap->id,
                    'district_id' => $gunturDistrict->id,
                ]
            );
        }

        // Map Los Angeles city bounds
        $laCity = City::where('name', 'Los Angeles')->first();
        if ($laCity) {
            $laDistrict = $laCity->district;
            $ca = $laDistrict->state;
            $usa = $ca->country;

            BranchCoverageArea::updateOrCreate(
                ['branch_id' => $branch2->id, 'city_id' => $laCity->id],
                [
                    'country_id' => $usa->id,
                    'state_id' => $ca->id,
                    'district_id' => $laDistrict->id,
                ]
            );
        }
    }
}
