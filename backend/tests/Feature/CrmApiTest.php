<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\CRM\Domain\Entities\Account;
use App\Modules\CRM\Domain\Entities\Contact;
use App\Modules\CRM\Domain\Entities\Lead;
use App\Modules\CRM\Domain\Entities\Opportunity;
use App\Modules\CRM\Domain\Entities\Quotation;
use App\Modules\CRM\Domain\Entities\PipelineStage;
use App\Modules\CRM\Domain\Entities\LostReason;
use App\Modules\CRM\Domain\Entities\FollowUp;
use App\Modules\CRM\Domain\Enums\LeadStatus;
use App\Modules\CRM\Domain\Enums\QuotationStatus;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Inventory\Domain\Entities\InventoryPricing;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderSubscription;
use Database\Seeders\GeographySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\CrmSeeder;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class CrmApiTest extends ApiTestCase
{
    protected string $branchId;
    protected string $faceId;
    protected string $pipelineStageId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(GeographySeeder::class);
        $this->seed(CrmSeeder::class);

        // Branch
        $branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'HQ Sales Branch',
            'code' => 'HQ-SB',
            'support_email' => 'sales@sodars.com',
            'support_phone' => '+91800100300',
        ]);
        $this->branchId = $branch->id;

        // Provider
        $provider = Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Apex Media',
            'registration_number' => 'REG-APEX-PRV',
            'provider_code' => 'APX-PRV-01',
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
            'inventory_code' => 'INV-APX-001',
            'display_name' => 'Apex Billboard 1',
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

        // Face
        $face = InventoryFace::create([
            'id' => (string) Str::uuid(),
            'inventory_id' => $inventory->id,
            'face_code' => 'INV-APX-001-F1',
            'display_name' => 'Front Face',
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

        // Pricing
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

        $this->pipelineStageId = PipelineStage::where('name', 'Proposal')->first()->id;
    }

    public function test_lead_creation_and_auto_scoring(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/v1/leads', [
            'title' => 'National Airport Campaign',
            'source' => 'referral',
        ]);

        $this->assertApiResponse($response, 201);
        $response->assertJsonPath('data.title', 'National Airport Campaign');
        
        // Auto score should be calculated: referral (40) + 'airport' term (30) = 70 points
        $response->assertJsonPath('data.lead_score', 70);
    }

    public function test_lead_qualification_creates_account_and_contact(): void
    {
        $this->actingAsAdmin();

        $lead = Lead::create([
            'id' => (string) Str::uuid(),
            'title' => 'Enterprise Media campaign',
            'source' => 'website',
            'status' => 'new',
            'lead_score' => 30,
        ]);

        $response = $this->postJson("/api/v1/leads/{$lead->id}/qualify");

        $this->assertApiResponse($response, 200);
        $response->assertJsonPath('data.status', 'qualified');

        $updatedLead = Lead::findOrFail($lead->id);
        $this->assertNotNull($updatedLead->account_id);
        $this->assertNotNull($updatedLead->contact_id);

        $account = Account::find($updatedLead->account_id);
        $this->assertEquals("Company of Enterprise Media campaign", $account->name);
    }

    public function test_quotation_creation_and_details(): void
    {
        $this->actingAsAdmin();

        $account = Account::create(['id' => (string) Str::uuid(), 'name' => 'Corporate Customer Ltd']);

        $response = $this->postJson('/api/v1/quotations', [
            'account_id' => $account->id,
            'quotation_number' => 'QT-2026-0001',
            'valid_until' => now()->addDays(15)->toDateString(),
            'subtotal_cents' => 300000,
            'discount_cents' => 0,
            'tax_cents' => 54000,
            'grand_total_cents' => 354000,
            'currency' => 'INR',
            'items' => [
                [
                    'inventory_face_id' => $this->faceId,
                    'start_date' => now()->addDays(5)->toDateString(),
                    'end_date' => now()->addDays(10)->toDateString(),
                    'daily_frequency' => 1,
                    'price_cents' => 300000,
                ]
            ]
        ]);

        $this->assertApiResponse($response, 201);
        $response->assertJsonPath('data.quotation_number', 'QT-2026-0001');

        $quoteId = $response->json('data.id');

        $showResponse = $this->getJson("/api/v1/quotations/{$quoteId}");
        $this->assertApiResponse($showResponse, 200);
        $showResponse->assertJsonPath('data.active_version_number', 1);
    }

    public function test_convert_accepted_quotation_to_booking(): void
    {
        $admin = $this->actingAsAdmin();

        $account = Account::create(['id' => (string) Str::uuid(), 'name' => 'Elite Clients Pvt Ltd']);

        // Create a quotation and force it to accepted status for conversion testing
        $quote = Quotation::create([
            'id' => (string) Str::uuid(),
            'account_id' => $account->id,
            'quotation_number' => 'QT-2026-9999',
            'status' => 'draft',
            'active_version_number' => 1,
        ]);

        $version = \App\Modules\CRM\Domain\Entities\QuotationVersion::create([
            'id' => (string) Str::uuid(),
            'quotation_id' => $quote->id,
            'version_number' => 1,
            'valid_until' => now()->addDays(30)->toDateString(),
            'subtotal_cents' => 500000,
            'discount_cents' => 0,
            'tax_cents' => 90000,
            'grand_total_cents' => 590000,
            'currency' => 'INR',
            'is_active' => true,
        ]);

        \App\Modules\CRM\Domain\Entities\QuotationItem::create([
            'id' => (string) Str::uuid(),
            'quotation_version_id' => $version->id,
            'inventory_face_id' => $this->faceId,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(20),
            'daily_frequency' => 1,
            'price_cents' => 500000,
        ]);

        // Set Quotation status to accepted
        $quote->update(['status' => QuotationStatus::ACCEPTED]);

        // Convert the quote to booking
        $response = $this->postJson("/api/v1/quotations/{$quote->id}/convert", [
            'branch_id' => $this->branchId,
            'customer_id' => $admin->id,
        ]);

        $this->assertApiResponse($response, 201);
        
        $bookingId = $response->json('data.id');
        $this->assertNotNull($bookingId);

        // Verify separate snapshots on booking
        $booking = Booking::findOrFail($bookingId);
        $this->assertNotNull($booking->quotation_snapshot);
        $this->assertNotNull($booking->booking_snapshot);
        $this->assertEquals('QT-2026-9999', $booking->quotation_snapshot['quotation_number']);
        $this->assertEquals($quote->id, $booking->quotation_id);
        $this->assertEquals($version->id, $booking->quotation_version_id);
        $this->assertNotNull($booking->converted_from_quotation_at);

        // Verify dynamic block was registered in availability
        $avail = \App\Modules\Inventory\Domain\Entities\InventoryAvailability::where('inventory_face_id', $this->faceId)
            ->where('availability_status', 'blocked')
            ->first();
        $this->assertNotNull($avail);
    }
}
