<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create permissions
        $permissions = [
            // Settings
            'settings.view', 'settings.update',
            // Geography
            'geography.view', 'geography.manage',
            // Branches
            'branch.create', 'branch.edit', 'branch.delete', 'branch.view',
            // Providers
            'provider.create', 'provider.edit', 'provider.delete', 'provider.view', 'provider.approve',
            // Inventory
            'inventory.create', 'inventory.edit', 'inventory.delete', 'inventory.view', 'inventory.approve',
            // Bookings
            'booking.create', 'booking.edit', 'booking.delete', 'booking.view', 'booking.approve', 'booking.reject', 'booking.cancel', 'booking.view_finance',
            // Campaigns
            'campaign.create', 'campaign.edit', 'campaign.delete', 'campaign.view', 'campaign.start', 'campaign.complete',
            // Notifications
            'notification.view', 'notification.send',
        ];

        foreach ($permissions as $permissionName) {
            Permission::create([
                'id' => (string) Str::uuid(),
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // 2. Create roles and assign permissions
        $superAdmin = Role::create([
            'id' => (string) Str::uuid(),
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);
        // Super Admin gets all permissions via gate bypass before callback, but assign explicitly too
        $superAdmin->givePermissionTo(Permission::all());

        $branchManager = Role::create([
            'id' => (string) Str::uuid(),
            'name' => 'branch_manager',
            'guard_name' => 'web',
        ]);
        $branchManager->givePermissionTo([
            'branch.view', 'provider.view', 'inventory.view', 'inventory.approve',
            'booking.view', 'booking.approve', 'booking.reject', 'campaign.view', 'notification.view',
        ]);

        $providerAdmin = Role::create([
            'id' => (string) Str::uuid(),
            'name' => 'provider_admin',
            'guard_name' => 'web',
        ]);
        $providerAdmin->givePermissionTo([
            'provider.view', 'inventory.create', 'inventory.edit', 'inventory.view',
            'booking.view', 'booking.approve', 'campaign.view', 'campaign.start', 'campaign.complete',
        ]);

        Role::create(['id' => (string) Str::uuid(), 'name' => 'provider_staff', 'guard_name' => 'web'])
            ->givePermissionTo(['inventory.view', 'booking.view', 'campaign.view']);

        $customerAdmin = Role::create([
            'id' => (string) Str::uuid(),
            'name' => 'customer_admin',
            'guard_name' => 'web',
        ]);
        $customerAdmin->givePermissionTo([
            'booking.create', 'booking.edit', 'booking.view', 'booking.cancel', 'campaign.view',
        ]);

        Role::create(['id' => (string) Str::uuid(), 'name' => 'customer_staff', 'guard_name' => 'web'])
            ->givePermissionTo(['booking.view', 'campaign.view']);

        Role::create(['id' => (string) Str::uuid(), 'name' => 'field_staff', 'guard_name' => 'web'])
            ->givePermissionTo(['campaign.view']);
    }
}
