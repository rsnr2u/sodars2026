<?php

declare(strict_types=1);

namespace App\Modules\Providers\Infrastructure\Database\Seeders;

use App\Models\User;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderAddress;
use App\Modules\Providers\Domain\Entities\ProviderBankAccount;
use App\Modules\Providers\Domain\Entities\ProviderContact;
use App\Modules\Providers\Domain\Entities\ProviderDocument;
use App\Modules\Providers\Domain\Entities\ProviderSetting;
use App\Modules\Providers\Domain\Entities\ProviderStaff;
use App\Modules\Providers\Domain\Entities\ProviderSubscription;
use App\Modules\Providers\Domain\Enums\BillingCycle;
use App\Modules\Providers\Domain\Enums\ContactType;
use App\Modules\Providers\Domain\Enums\DocumentStatus;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\ValueObjects\ProviderSettings;
use App\Platform\Shared\Domain\Entities\City;
use App\Platform\Shared\Domain\Entities\Country;
use App\Platform\Shared\Domain\Entities\District;
use App\Platform\Shared\Domain\Entities\Pincode;
use App\Platform\Shared\Domain\Entities\State;
use App\Platform\Shared\Domain\Entities\MediaLibrary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ProviderSeeder extends Seeder
{
    /**
     * Run registration seeds.
     */
    public function run(): void
    {
        $branch = Branch::first();
        if (!$branch) {
            return;
        }

        $country = Country::first();
        $state = State::first();
        $district = District::first();
        $city = City::first();
        $pincode = Pincode::first();

        // 1. Ensure Spatie roles are generated using explicit UUIDs
        Role::firstOrCreate(
            ['name' => 'provider_admin', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );
        Role::firstOrCreate(
            ['name' => 'provider_staff', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        // 2. Create provider
        /** @var Provider $provider */
        $provider = Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Acme Advertising Media',
            'registration_number' => 'REG-123456789',
            'provider_code' => 'PRV-000001',
            'default_branch_id' => $branch->id,
            'status' => ProviderStatus::Verified->value,
            'preferred_payout_method' => 'bank',
        ]);

        // 3. Create address
        ProviderAddress::create([
            'id' => (string) Str::uuid(),
            'provider_id' => $provider->id,
            'country_id' => $country?->id,
            'state_id' => $state?->id,
            'district_id' => $district?->id,
            'city_id' => $city?->id,
            'pincode_id' => $pincode?->id,
            'address_line1' => 'Suite 500, Tech Tower',
            'address_line2' => 'Industrial Area',
            'is_primary' => true,
        ]);

        // 4. Create owner contact
        ProviderContact::create([
            'id' => (string) Str::uuid(),
            'provider_id' => $provider->id,
            'contact_name' => 'John Doe',
            'email' => 'john.doe@acme.com',
            'phone' => '+1234567890',
            'type' => ContactType::Owner->value,
        ]);

        // 5. Create Settings
        ProviderSetting::create([
            'id' => (string) Str::uuid(),
            'provider_id' => $provider->id,
            'settings' => new ProviderSettings(
                marketplaceEnabled: true,
                bookingNotifications: true,
                email: true,
                sms: true
            ),
        ]);

        // 6. Create Bank Account
        ProviderBankAccount::create([
            'id' => (string) Str::uuid(),
            'provider_id' => $provider->id,
            'bank_name' => 'Federal Credit Bank',
            'account_holder' => 'Acme Advertising Media Ltd',
            'account_number' => '100020003000',
            'routing_code' => 'FEDR0001',
            'is_primary' => true,
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        // 7. Create Subscription limits
        ProviderSubscription::create([
            'id' => (string) Str::uuid(),
            'provider_id' => $provider->id,
            'subscription_plan_id' => null,
            'max_active_screens' => 10,
            'billing_cycle' => BillingCycle::Monthly->value,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        // 8. Create Admin User
        $user = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'John Doe',
            'email' => 'john.doe@acme.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('provider_admin');

        // Create Staff Membership
        ProviderStaff::create([
            'id' => (string) Str::uuid(),
            'provider_id' => $provider->id,
            'user_id' => $user->id,
            'is_primary' => true,
            'is_active' => true,
            'joined_at' => now(),
        ]);

        // 9. Create verified compliance document
        /** @var ProviderDocument $doc */
        $doc = ProviderDocument::create([
            'id' => (string) Str::uuid(),
            'provider_id' => $provider->id,
            'document_type' => 'business_registry',
            'status' => DocumentStatus::Approved->value,
            'version' => 1,
            'is_current' => true,
            'verified_at' => now(),
        ]);

        // Create central polymorphic media reference
        MediaLibrary::create([
            'id' => (string) Str::uuid(),
            'file_name' => 'business_registry.pdf',
            'file_path' => 'uploads/documents/business_registry.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'mediable_type' => ProviderDocument::class,
            'mediable_id' => $doc->id,
        ]);
    }
}
