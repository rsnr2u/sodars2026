<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Finance\Domain\Entities\Payment;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Inventory\Domain\Entities\InventoryPricing;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderSubscription;
use Database\Seeders\GeographySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class BookingApiTest extends ApiTestCase
{
    protected string $branchId;
    protected string $faceId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(GeographySeeder::class);

        // Branch
        $branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'City HQ Branch',
            'code' => 'CHQ-B',
            'support_email' => 'cityhq@sodars.com',
            'support_phone' => '+91800100300',
        ]);
        $this->branchId = $branch->id;

        // Provider
        $provider = Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Metropolis Media',
            'registration_number' => 'REG-METRO-PRV',
            'provider_code' => 'MET-PRV-01',
            'default_branch_id' => $branch->id,
            'status' => 'verified',
            'preferred_payout_method' => 'bank',
        ]);

        ProviderSubscription::create([
            'id' => (string) Str::uuid(),
            'provider_id' => $provider->id,
            'max_active_screens' => 10,
            'billing_cycle' => 'monthly',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        $country = \App\Platform\Shared\Domain\Entities\Country::first();
        $state = \App\Platform\Shared\Domain\Entities\State::first();
        $district = \App\Platform\Shared\Domain\Entities\District::first();
        $city = \App\Platform\Shared\Domain\Entities\City::first();
        $pincode = \App\Platform\Shared\Domain\Entities\Pincode::first();

        // Inventory
        $inventory = Inventory::create([
            'id' => (string) Str::uuid(),
            'inventory_code' => 'INV-METRO-001',
            'display_name' => 'Metro Billboard 1',
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
            'latitude' => 17.4422,
            'longitude' => 78.3499,
            'geo_hash' => 'te7u61gh',
            'normalized_address' => 'Madhapur, Hyderabad',
            'status' => 'approved',
            'ai_scores' => [
                'visibility_score' => 85,
                'traffic_score' => 85,
                'engagement_score' => 85,
                'overall_score' => 85
            ],
            'inventory_capabilities' => [
                'supportsAudio' => false,
                'supportsVideo' => false,
                'supportsInteractive' => false,
                'supportsProgrammatic' => false,
                'hasLighting' => true,
                'hasCamera' => false,
                'hasWifi' => false,
                'maxResolutionWidth' => null,
                'maxResolutionHeight' => null
            ]
        ]);

        // Inventory Face
        $face = InventoryFace::create([
            'id' => (string) Str::uuid(),
            'inventory_id' => $inventory->id,
            'face_code' => 'INV-METRO-001-F1',
            'display_name' => 'Front Side',
            'facing_direction' => 'north',
            'display_order' => 1,
            'is_active' => true,
            'physical_specifications' => [
                'width_cm' => 1200,
                'height_cm' => 600,
                'orientation' => 'landscape',
                'illuminated' => true
            ]
        ]);
        $this->faceId = $face->id;

        // Pricing Configuration
        InventoryPricing::create([
            'id' => (string) Str::uuid(),
            'inventory_face_id' => $face->id,
            'rate_cents' => 300000, // 3000 INR per day
            'currency' => 'INR',
            'pricing_type' => 'baseline',
            'effective_from' => now()->subDays(5)->toDateString(),
            'effective_to' => null,
            'markup_percentage' => 10,
            'priority' => 1,
        ]);
    }

    public function test_customer_can_checkout_booking(): void
    {
        $admin = $this->actingAsAdmin();

        $response = $this->postJson('/api/v1/bookings', [
            'customer_id' => $admin->id,
            'branch_id' => $this->branchId,
            'currency' => 'INR',
            'items' => [
                [
                    'inventory_face_id' => $this->faceId,
                    'start_date' => now()->addDays(5)->toDateString(),
                    'end_date' => now()->addDays(9)->toDateString(), // 5 days flight
                    'daily_frequency' => 1,
                ]
            ]
        ]);

        $this->assertApiResponse($response, 201);
        $response->assertJsonPath('data.status', 'draft');
        $this->assertNotNull($response->json('data.booking_code'));

        $bookingId = $response->json('data.id');
        $this->assertDatabaseHas('booking_items', [
            'booking_id' => $bookingId,
            'inventory_face_id' => $this->faceId,
        ]);
    }

    public function test_workflow_payment_auditing_and_approval(): void
    {
        $admin = $this->actingAsAdmin();

        $checkoutResponse = $this->postJson('/api/v1/bookings', [
            'customer_id' => $admin->id,
            'branch_id' => $this->branchId,
            'currency' => 'INR',
            'items' => [
                [
                    'inventory_face_id' => $this->faceId,
                    'start_date' => now()->addDays(5)->toDateString(),
                    'end_date' => now()->addDays(9)->toDateString(),
                ]
            ]
        ]);

        $bookingId = $checkoutResponse->json('data.id');

        // 1. Record offline UPI payment
        $paymentResponse = $this->postJson("/api/v1/bookings/{$bookingId}/payments", [
            'payment_method' => 'upi',
            'amount_cents' => 1650000,
            'reference_number' => 'UPI-1234567890',
            'notes' => 'payment receipt screenshot file path uploads/payment.jpg',
        ]);

        $this->assertApiResponse($paymentResponse, 201);
        $paymentId = $paymentResponse->json('data.id');

        $this->assertDatabaseHas('payments', [
            'id' => $paymentId,
            'paymentable_id' => $bookingId,
            'paymentable_type' => Booking::class,
            'status' => 'pending',
        ]);

        // Booking status shifted to branch_review automatically upon payment upload
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => 'branch_review',
        ]);

        // 2. Branch manager verifies the payment
        $auditPaymentResponse = $this->patchJson("/api/v1/bookings/{$bookingId}/payments/{$paymentId}/audit", [
            'status' => 'verified',
        ]);

        $this->assertApiResponse($auditPaymentResponse, 200);

        // Booking status shifted to provider_review automatically upon payment verification
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => 'provider_review',
        ]);

        // 3. Provider/Admin approves availability & schedules flight
        $auditBookingResponse = $this->patchJson("/api/v1/bookings/{$bookingId}/audit", [
            'status' => 'approved',
            'comment' => 'Confirmed display slots.',
        ]);

        $this->assertApiResponse($auditBookingResponse, 200);
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => 'approved',
        ]);

        // Check availability ledger reserves slots
        $this->assertDatabaseHas('inventory_availability', [
            'inventory_face_id' => $this->faceId,
            'availability_status' => 'reserved',
        ]);
    }

    public function test_booking_rejection_and_cancellation(): void
    {
        $admin = $this->actingAsAdmin();

        $checkoutResponse = $this->postJson('/api/v1/bookings', [
            'customer_id' => $admin->id,
            'branch_id' => $this->branchId,
            'currency' => 'INR',
            'items' => [
                [
                    'inventory_face_id' => $this->faceId,
                    'start_date' => now()->addDays(5)->toDateString(),
                    'end_date' => now()->addDays(9)->toDateString(),
                ]
            ]
        ]);

        $bookingId = $checkoutResponse->json('data.id');

        // Cancel booking
        $cancelResponse = $this->patchJson("/api/v1/bookings/{$bookingId}/audit", [
            'status' => 'cancelled',
            'comment' => 'Cancelled by advertiser.',
        ]);

        $this->assertApiResponse($cancelResponse, 200);
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => 'cancelled',
        ]);

        // Ledger check releases slots
        $this->assertDatabaseMissing('inventory_availability', [
            'inventory_face_id' => $this->faceId,
            'availability_status' => 'blocked',
            'deleted_at' => null,
        ]);
    }
}
