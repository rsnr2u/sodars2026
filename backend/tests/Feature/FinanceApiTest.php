<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Entities\BookingItem;
use App\Modules\Bookings\Domain\Entities\Payment;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Entities\ProviderSettlement;
use App\Modules\Finance\Domain\Entities\RevenueRecognitionSchedule;
use App\Modules\Finance\Domain\Entities\RevenueRecognitionEntry;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Inventory\Domain\Entities\InventoryPricing;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderSubscription;
use Database\Seeders\GeographySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class FinanceApiTest extends ApiTestCase
{
    protected string $branchId;
    protected string $faceId;
    protected string $customerStateId;
    protected string $branchStateId;
    protected string $providerId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(GeographySeeder::class);

        $country = \App\Platform\Shared\Domain\Entities\Country::first();
        $state = \App\Platform\Shared\Domain\Entities\State::first();
        $district = \App\Platform\Shared\Domain\Entities\District::first();
        $city = \App\Platform\Shared\Domain\Entities\City::first();
        $pincode = \App\Platform\Shared\Domain\Entities\Pincode::first();

        $this->branchStateId = $state?->id ?? (string) Str::uuid();

        // Branch
        $branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'City HQ Branch',
            'code' => 'CHQ-B',
            'support_email' => 'cityhq@sodars.com',
            'support_phone' => '+91800100300',
            'state_id' => $this->branchStateId,
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
        $this->providerId = $provider->id;

        ProviderSubscription::create([
            'id' => (string) Str::uuid(),
            'provider_id' => $provider->id,
            'max_active_screens' => 10,
            'billing_cycle' => 'monthly',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ]);

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
            'rate_cents' => 300000,
            'currency' => 'INR',
            'pricing_type' => 'baseline',
            'effective_from' => now()->subDays(5)->toDateString(),
            'effective_to' => null,
            'markup_percentage' => 10,
            'priority' => 1,
        ]);
    }

    public function test_booking_approved_auto_generates_proforma_invoice(): void
    {
        $admin = $this->actingAsAdmin();
        $admin->update(['state_id' => $this->branchStateId]); // same state -> CGST/SGST

        // 1. Checkout Booking
        $checkoutResponse = $this->postJson('/api/v1/bookings', [
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

        $this->assertApiResponse($checkoutResponse, 201);
        $bookingId = $checkoutResponse->json('data.id');

        // Check proforma invoice automatically generated in draft status
        $this->assertDatabaseHas('invoices', [
            'booking_id' => $bookingId,
            'invoice_type' => 'proforma_invoice',
            'status' => 'draft',
            'currency' => 'INR',
        ]);

        $invoice = Invoice::where('booking_id', $bookingId)->where('invoice_type', 'proforma_invoice')->first();
        
        // Assert complete booking snapshot immutability
        $this->assertNotNull($invoice->booking_snapshot['booking_number']);
        $this->assertEquals('City HQ Branch', $invoice->booking_snapshot['branch']['name']);
        
        // Assert CGST/SGST split was correctly calculated
        $this->assertDatabaseHas('invoice_taxes', [
            'invoice_id' => $invoice->id,
            'tax_name' => 'CGST',
            'tax_rate_percentage' => 9.00,
        ]);
    }

    public function test_payment_auditing_auto_generates_issued_tax_invoice_and_settlement(): void
    {
        $admin = $this->actingAsAdmin();
        $admin->update(['email' => 'interstate-customer@example.com']);

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

        // Record payment
        $paymentResponse = $this->postJson("/api/v1/bookings/{$bookingId}/payments", [
            'payment_method' => 'upi',
            'amount_cents' => 1650000,
            'reference_number' => 'UPI-1234567890',
        ]);

        $paymentId = $paymentResponse->json('data.id');

        // Verify/Audit payment -> will fire event and generate Tax Invoice + Provider Settlement + Revenue Recognition schedules
        $auditResponse = $this->patchJson("/api/v1/bookings/{$bookingId}/payments/{$paymentId}/audit", [
            'status' => 'verified',
        ]);
        $this->assertApiResponse($auditResponse, 200);

        // Assert Tax Invoice issued
        $this->assertDatabaseHas('invoices', [
            'booking_id' => $bookingId,
            'invoice_type' => 'tax_invoice',
            'status' => 'issued',
        ]);

        $taxInvoice = Invoice::where('booking_id', $bookingId)->where('invoice_type', 'tax_invoice')->first();

        // Assert IGST split was correctly calculated
        $this->assertDatabaseHas('invoice_taxes', [
            'invoice_id' => $taxInvoice->id,
            'tax_name' => 'IGST',
            'tax_rate_percentage' => 18.00,
        ]);

        // Assert Provider Settlement aggregate created
        $this->assertDatabaseHas('provider_settlements', [
            'booking_id' => $bookingId,
            'invoice_id' => $taxInvoice->id,
            'status' => 'pending',
        ]);

        $settlement = ProviderSettlement::where('booking_id', $bookingId)->first();
        $this->assertNotNull($settlement->settlement_number);

        // Assert Revenue Recognition expected daily schedules created (5 days flight -> 5 pending schedules)
        $this->assertEquals(5, RevenueRecognitionSchedule::where('booking_id', $bookingId)->where('status', 'pending')->count());
    }

    public function test_invoice_adjustments_and_payments_endpoints(): void
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
        $invoice = Invoice::where('booking_id', $bookingId)->first();

        // 1. Create Credit Note adjustment
        $adjustmentResponse = $this->postJson("/api/v1/invoices/{$invoice->id}/adjustments", [
            'adjustment_type' => 'credit',
            'amount_cents' => 50000,
            'reason' => 'Goodwill waiver discount',
        ]);

        $this->assertApiResponse($adjustmentResponse, 200);
        $this->assertDatabaseHas('invoice_adjustments', [
            'invoice_id' => $invoice->id,
            'adjustment_type' => 'credit',
            'amount_cents' => 50000,
        ]);

        // 2. Allocate payment to invoice
        $payResponse = $this->postJson("/api/v1/invoices/{$invoice->id}/payments", [
            'amount_cents' => 10000000,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'REF-WIRE-98765',
        ]);

        $this->assertApiResponse($payResponse, 200);
        
        // Assert invoice is marked as paid
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);
    }

    public function test_revenue_recognition_engine_deferred_allocations(): void
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
        $paymentResponse = $this->postJson("/api/v1/bookings/{$bookingId}/payments", [
            'payment_method' => 'upi',
            'amount_cents' => 1650000,
            'reference_number' => 'UPI-1234567890',
        ]);

        $paymentId = $paymentResponse->json('data.id');

        $this->patchJson("/api/v1/bookings/{$bookingId}/payments/{$paymentId}/audit", [
            'status' => 'verified',
        ]);

        // Booking is approved & scheduled, schedules populated. Let's recognize revenue!
        // Retrieve target recognition date
        $targetDate = now()->addDays(6)->toDateString();

        $recognizeResponse = $this->postJson('/api/v1/invoices/recognize-revenue', [
            'as_of_date' => $targetDate,
        ]);

        $this->assertApiResponse($recognizeResponse, 200);
        
        // Ensure recognition entries are posted
        $this->assertDatabaseHas('revenue_recognition_entries', [
            'status' => 'recognized',
        ]);

        // Assert analytics shows correct breakdown
        $analyticsResponse = $this->getJson("/api/v1/invoices/revenue-analytics?booking_id={$bookingId}");
        $this->assertApiResponse($analyticsResponse, 200);
        $this->assertGreaterThan(0, $analyticsResponse->json('data.earned_revenue_cents'));
        $this->assertGreaterThan(0, $analyticsResponse->json('data.deferred_revenue_cents'));
    }
}
