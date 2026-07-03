<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Database\Seeders;

use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Entities\InventoryActivity;
use App\Modules\Inventory\Domain\Entities\InventoryAvailability;
use App\Modules\Inventory\Domain\Entities\InventoryDocument;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Inventory\Domain\Entities\InventoryPricing;
use App\Modules\Inventory\Domain\ValueObjects\GeoLocation;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Platform\Shared\Domain\Entities\City;
use App\Platform\Shared\Domain\Entities\Country;
use App\Platform\Shared\Domain\Entities\District;
use App\Platform\Shared\Domain\Entities\MediaLibrary;
use App\Platform\Shared\Domain\Entities\Pincode;
use App\Platform\Shared\Domain\Entities\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    /**
     * Seed demo inventory structures with faces, pricing, and availability.
     */
    public function run(): void
    {
        $provider = Provider::first();
        $branch = Branch::first();
        if (!$provider || !$branch) {
            $this->command->warn('Skipping InventorySeeder: Provider or Branch not found. Run ProviderSeeder and BranchSeeder first.');
            return;
        }

        $country = Country::first();
        $state = State::first();
        $district = District::first();
        $city = City::first();
        $pincode = Pincode::first();

        // ─── Inventory 1: Static Billboard ───────────────────────────

        $geo1 = new GeoLocation(17.4401, 78.3489);

        /** @var Inventory $inv1 */
        $inv1 = Inventory::create([
            'id' => (string) Str::uuid(),
            'inventory_code' => 'INV-STA-000001',
            'display_name' => 'Hitec City Junction Billboard',
            'provider_id' => $provider->id,
            'branch_id' => $branch->id,
            'country_id' => $country?->id,
            'state_id' => $state?->id,
            'district_id' => $district?->id,
            'city_id' => $city?->id,
            'pincode_id' => $pincode?->id,
            'inventory_category' => 'Static',
            'inventory_type' => 'Billboard',
            'ownership_type' => 'owned',
            'latitude' => 17.4401,
            'longitude' => 78.3489,
            'geo_hash' => $geo1->geoHash,
            'normalized_address' => 'Hitec City Junction, Hyderabad, Telangana 500081',
            'search_keywords' => 'hitec city billboard hoarding highway',
            'status' => 'approved',
            'marketplace_enabled' => true,
            'is_featured' => true,
            'accepts_programmatic_booking' => false,
            'visibility' => 'public',
            'inventory_capabilities' => [
                'supportsAudio' => false,
                'supportsVideo' => false,
                'supportsInteractive' => false,
                'supportsProgrammatic' => false,
                'hasLighting' => true,
                'hasCamera' => false,
                'hasWifi' => false,
                'maxResolutionWidth' => null,
                'maxResolutionHeight' => null,
            ],
            'ai_scores' => [
                'visibility_score' => 88,
                'traffic_score' => 92,
                'engagement_score' => 75,
                'overall_score' => 85,
            ],
        ]);

        // Face A (Front)
        $face1A = InventoryFace::create([
            'id' => (string) Str::uuid(),
            'inventory_id' => $inv1->id,
            'face_code' => 'INV-STA-000001-F1',
            'display_name' => 'Front Face',
            'facing_direction' => 'north',
            'display_order' => 1,
            'physical_specifications' => [
                'width_cm' => 1200,
                'height_cm' => 600,
                'orientation' => 'landscape',
                'illuminated' => true,
            ],
            'is_active' => true,
        ]);

        // Face B (Rear)
        $face1B = InventoryFace::create([
            'id' => (string) Str::uuid(),
            'inventory_id' => $inv1->id,
            'face_code' => 'INV-STA-000001-F2',
            'display_name' => 'Rear Face',
            'facing_direction' => 'south',
            'display_order' => 2,
            'physical_specifications' => [
                'width_cm' => 1200,
                'height_cm' => 600,
                'orientation' => 'landscape',
                'illuminated' => false,
            ],
            'is_active' => true,
        ]);

        // Pricing for Face A
        InventoryPricing::create([
            'id' => (string) Str::uuid(),
            'inventory_face_id' => $face1A->id,
            'pricing_type' => 'baseline',
            'rate_cents' => 75000,
            'currency' => 'INR',
            'tax_inclusive' => false,
            'minimum_booking_days' => 7,
            'effective_from' => now(),
            'effective_to' => null,
            'priority' => 0,
        ]);

        // Pricing for Face B (lower rate)
        InventoryPricing::create([
            'id' => (string) Str::uuid(),
            'inventory_face_id' => $face1B->id,
            'pricing_type' => 'baseline',
            'rate_cents' => 45000,
            'currency' => 'INR',
            'tax_inclusive' => false,
            'minimum_booking_days' => 7,
            'effective_from' => now(),
            'effective_to' => null,
            'priority' => 0,
        ]);

        // Availability for both faces
        foreach ([$face1A, $face1B] as $face) {
            InventoryAvailability::create([
                'id' => (string) Str::uuid(),
                'inventory_face_id' => $face->id,
                'start_at' => now(),
                'end_at' => now()->addYears(50),
                'availability_status' => 'operational',
                'reason' => 'Operational',
                'source' => 'System',
                'remarks' => 'Default operational slot created during seeding.',
            ]);
        }

        // ─── Inventory 2: Digital Screen ─────────────────────────────

        $geo2 = new GeoLocation(17.4260, 78.4480);

        /** @var Inventory $inv2 */
        $inv2 = Inventory::create([
            'id' => (string) Str::uuid(),
            'inventory_code' => 'INV-DIG-000002',
            'display_name' => 'Begumpet Airport Road LED',
            'provider_id' => $provider->id,
            'branch_id' => $branch->id,
            'country_id' => $country?->id,
            'state_id' => $state?->id,
            'district_id' => $district?->id,
            'city_id' => $city?->id,
            'pincode_id' => $pincode?->id,
            'inventory_category' => 'Digital',
            'inventory_type' => 'LED Screen',
            'ownership_type' => 'leased',
            'latitude' => 17.4260,
            'longitude' => 78.4480,
            'geo_hash' => $geo2->geoHash,
            'normalized_address' => 'Airport Road, Begumpet, Hyderabad, Telangana 500016',
            'search_keywords' => 'digital led screen airport begumpet',
            'status' => 'approved',
            'marketplace_enabled' => true,
            'is_featured' => false,
            'accepts_programmatic_booking' => true,
            'visibility' => 'public',
            'inventory_capabilities' => [
                'supportsAudio' => true,
                'supportsVideo' => true,
                'supportsInteractive' => false,
                'supportsProgrammatic' => true,
                'hasLighting' => true,
                'hasCamera' => true,
                'hasWifi' => true,
                'maxResolutionWidth' => 1920,
                'maxResolutionHeight' => 1080,
            ],
            'ai_scores' => [
                'visibility_score' => 95,
                'traffic_score' => 89,
                'engagement_score' => 91,
                'overall_score' => 92,
            ],
        ]);

        $face2 = InventoryFace::create([
            'id' => (string) Str::uuid(),
            'inventory_id' => $inv2->id,
            'face_code' => 'INV-DIG-000002-F1',
            'display_name' => 'Main Screen',
            'facing_direction' => 'east',
            'display_order' => 1,
            'physical_specifications' => [
                'width_cm' => 500,
                'height_cm' => 300,
                'orientation' => 'landscape',
                'illuminated' => true,
            ],
            'is_active' => true,
        ]);

        InventoryPricing::create([
            'id' => (string) Str::uuid(),
            'inventory_face_id' => $face2->id,
            'pricing_type' => 'baseline',
            'rate_cents' => 125000,
            'currency' => 'INR',
            'tax_inclusive' => false,
            'minimum_booking_days' => 1,
            'effective_from' => now(),
            'effective_to' => null,
            'priority' => 0,
        ]);

        // Seasonal premium pricing
        InventoryPricing::create([
            'id' => (string) Str::uuid(),
            'inventory_face_id' => $face2->id,
            'pricing_type' => 'seasonal',
            'rate_cents' => 175000,
            'currency' => 'INR',
            'tax_inclusive' => false,
            'minimum_booking_days' => 1,
            'effective_from' => now()->addMonths(3),
            'effective_to' => now()->addMonths(4),
            'priority' => 10,
        ]);

        InventoryAvailability::create([
            'id' => (string) Str::uuid(),
            'inventory_face_id' => $face2->id,
            'start_at' => now(),
            'end_at' => now()->addYears(50),
            'availability_status' => 'operational',
            'reason' => 'Operational',
            'source' => 'System',
            'remarks' => 'Default operational slot.',
        ]);

        // Compliance document for Inventory 1
        $doc = InventoryDocument::create([
            'id' => (string) Str::uuid(),
            'inventory_id' => $inv1->id,
            'document_type' => 'municipal_permit',
            'status' => 'approved',
        ]);

        MediaLibrary::create([
            'id' => (string) Str::uuid(),
            'file_name' => 'municipal_permit.pdf',
            'file_path' => 'uploads/inventory/documents/municipal_permit.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 2048,
            'mediable_type' => InventoryDocument::class,
            'mediable_id' => $doc->id,
        ]);

        // Activity log
        InventoryActivity::create([
            'id' => (string) Str::uuid(),
            'inventory_id' => $inv1->id,
            'performed_by' => null,
            'event_name' => 'inventory.created.v1',
            'action' => 'Created',
            'old_values' => null,
            'new_values' => $inv1->toArray(),
            'ip' => '127.0.0.1',
            'user_agent' => 'Seeder',
            'trace_id' => (string) Str::uuid(),
        ]);

        InventoryActivity::create([
            'id' => (string) Str::uuid(),
            'inventory_id' => $inv2->id,
            'performed_by' => null,
            'event_name' => 'inventory.created.v1',
            'action' => 'Created',
            'old_values' => null,
            'new_values' => $inv2->toArray(),
            'ip' => '127.0.0.1',
            'user_agent' => 'Seeder',
            'trace_id' => (string) Str::uuid(),
        ]);
    }
}
