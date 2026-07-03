<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Platform\Settings\Infrastructure\Database\Seeders\SettingsSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminSeeder::class,
            GeographySeeder::class,
            SettingsSeeder::class,
            \App\Platform\Accounting\Database\Seeders\ChartOfAccountsSeeder::class,
            \Database\Seeders\CrmSeeder::class,
            \Database\Seeders\DamSeeder::class,
        ]);
    }
}
