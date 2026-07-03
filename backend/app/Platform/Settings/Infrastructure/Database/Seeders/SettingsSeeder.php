<?php

declare(strict_types=1);

namespace App\Platform\Settings\Infrastructure\Database\Seeders;

use App\Platform\Settings\Domain\Entities\CompanyProfile;
use App\Platform\Settings\Domain\Entities\FeatureFlag;
use App\Platform\Settings\Domain\Entities\SystemSetting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed system settings
        $settings = [
            [
                'setting_key' => 'marketplace.markup_percent',
                'setting_value' => '15',
                'group_name' => 'marketplace',
                'category' => 'pricing',
                'is_encrypted' => false,
                'is_env_override' => true,
            ],
            [
                'setting_key' => 'tax.gst_percent',
                'setting_value' => '18',
                'group_name' => 'tax',
                'category' => 'finance',
                'is_encrypted' => false,
                'is_env_override' => false,
            ],
            [
                'setting_key' => 'security.session_lifetime_minutes',
                'setting_value' => '120',
                'group_name' => 'security',
                'category' => 'session',
                'is_encrypted' => false,
                'is_env_override' => false,
            ],
            [
                'setting_key' => 'maps.google_api_key',
                'setting_value' => 'mock_google_maps_key_123',
                'group_name' => 'maps',
                'category' => 'integrations',
                'is_encrypted' => true, // Encrypted on save!
                'is_env_override' => true,
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $setting['setting_key']],
                $setting
            );
        }

        // 2. Seed company profile
        CompanyProfile::updateOrCreate(
            ['legal_name' => 'SODARS Smart Outdoor Digital Asset Resource System Ltd.'],
            [
                'tax_number' => 'GSTIN9999AAAA1111',
                'address_line_1' => 'Head Office, Tech Park Area, Guntur',
                'city' => 'Guntur',
                'state' => 'Andhra Pradesh',
                'zip_code' => '522001',
                'logo_s3_path' => 'public/branding/logo.png',
                'primary_color' => '#1A365D',
                'secondary_color' => '#D69E2E',
            ]
        );

        // 3. Seed feature flags
        $flags = [
            ['flag_key' => 'marketplace.enabled', 'is_enabled' => true, 'description' => 'Enables search and booking cart'],
            ['flag_key' => 'notifications.sms', 'is_enabled' => false, 'description' => 'Enables SMS dispatch gateway'],
            ['flag_key' => 'notifications.whatsapp', 'is_enabled' => false, 'description' => 'Enables WhatsApp messaging gateway'],
            ['flag_key' => 'analytics.snapshots', 'is_enabled' => true, 'description' => 'Enables nightly stats snapshot jobs'],
        ];

        foreach ($flags as $flag) {
            FeatureFlag::updateOrCreate(
                ['flag_key' => $flag['flag_key']],
                $flag
            );
        }
    }
}
