<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Enums\InventoryStatus;
use App\Modules\Inventory\Domain\Enums\OwnershipType;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Enums\InvoiceStatus;
use App\Modules\Finance\Domain\Enums\InvoiceType;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Platform\Search\Application\Services\IndexingService;
use App\Platform\Search\Domain\Entities\SearchIndex;
use App\Platform\Search\Domain\Entities\SearchDocument;
use App\Platform\Search\Domain\Entities\SavedSearch;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use App\Platform\Shared\Domain\Entities\Country;
use App\Platform\Shared\Domain\Entities\State;
use App\Platform\Shared\Domain\Entities\District;
use App\Platform\Shared\Domain\Entities\City;
use App\Platform\Shared\Domain\Entities\Pincode;
use App\Modules\Providers\Domain\Entities\Provider;
use Database\Seeders\GeographySeeder;
use Tests\Core\ApiTestCase;

class SearchApiTest extends ApiTestCase
{
    use RefreshDatabase;

    protected IndexingService $indexingService;
    protected Branch $branch;
    protected User $admin;
    protected Provider $provider;
    protected Country $country;
    protected State $state;
    protected District $district;
    protected City $city;
    protected Pincode $pincode;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(GeographySeeder::class);

        $this->indexingService = app(IndexingService::class);

        $this->country = Country::firstOrFail();
        $this->state = State::firstOrFail();
        $this->district = District::firstOrFail();
        $this->city = City::firstOrFail();
        $this->pincode = Pincode::firstOrFail();

        // Create Admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        // Create Branch
        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'HQ Branch',
            'code' => 'HQ-B',
            'support_email' => 'hq@sodars.com',
            'support_phone' => '+91800100',
        ]);

        // Create a verified provider
        $this->provider = Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Test Media Corp',
            'registration_number' => 'REG-INV-TEST-001',
            'provider_code' => 'PRV-TEST-001',
            'default_branch_id' => $this->branch->id,
            'status' => 'verified',
            'preferred_payout_method' => 'bank',
        ]);
    }

    public function test_full_text_search_filtering_and_facets(): void
    {
        $this->actingAs($this->admin);

        // 1. Create three Inventories
        $inv1 = Inventory::create([
            'id' => (string) Str::uuid(),
            'inventory_code' => 'INV-A1',
            'display_name' => 'Metro Billboard Near Airport',
            'provider_id' => $this->provider->id,
            'branch_id' => $this->branch->id,
            'country_id' => $this->country->id,
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'city_id' => $this->city->id,
            'pincode_id' => $this->pincode->id,
            'inventory_category' => 'billboard',
            'inventory_type' => 'digital',
            'ownership_type' => OwnershipType::Owned,
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'geo_hash' => 'tdr123',
            'normalized_address' => 'Airport Road Bangalore',
            'search_keywords' => 'prime digital airport display',
            'status' => InventoryStatus::Approved,
            'ai_scores' => new \App\Modules\Inventory\Domain\ValueObjects\InventoryScore(),
            'inventory_capabilities' => new \App\Modules\Inventory\Domain\ValueObjects\InventoryCapabilities(),
        ]);

        $inv2 = Inventory::create([
            'id' => (string) Str::uuid(),
            'inventory_code' => 'INV-B2',
            'display_name' => 'Highstreet Kiosk Near Main Street',
            'provider_id' => $this->provider->id,
            'branch_id' => $this->branch->id,
            'country_id' => $this->country->id,
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'city_id' => $this->city->id,
            'pincode_id' => $this->pincode->id,
            'inventory_category' => 'kiosk',
            'inventory_type' => 'traditional',
            'ownership_type' => OwnershipType::Owned,
            'latitude' => 12.9717,
            'longitude' => 77.5947,
            'geo_hash' => 'tdr124',
            'normalized_address' => 'Main Street Mall',
            'search_keywords' => 'traditional mall kiosk',
            'status' => InventoryStatus::Approved,
            'ai_scores' => new \App\Modules\Inventory\Domain\ValueObjects\InventoryScore(),
            'inventory_capabilities' => new \App\Modules\Inventory\Domain\ValueObjects\InventoryCapabilities(),
        ]);

        $inv3 = Inventory::create([
            'id' => (string) Str::uuid(),
            'inventory_code' => 'INV-C3',
            'display_name' => 'Airport Bus Shelter Display',
            'provider_id' => $this->provider->id,
            'branch_id' => $this->branch->id,
            'country_id' => $this->country->id,
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'city_id' => $this->city->id,
            'pincode_id' => $this->pincode->id,
            'inventory_category' => 'shelter',
            'inventory_type' => 'digital',
            'ownership_type' => OwnershipType::Owned,
            'latitude' => 12.9718,
            'longitude' => 77.5948,
            'geo_hash' => 'tdr125',
            'normalized_address' => 'Bus Stand Airport Road',
            'search_keywords' => 'digital transit airport shelter',
            'status' => InventoryStatus::Draft,
            'ai_scores' => new \App\Modules\Inventory\Domain\ValueObjects\InventoryScore(),
            'inventory_capabilities' => new \App\Modules\Inventory\Domain\ValueObjects\InventoryCapabilities(),
        ]);

        // Manually trigger index (in production this runs via queue listener on event dispatch)
        $this->indexingService->index($inv1);
        $this->indexingService->index($inv2);
        $this->indexingService->index($inv3);

        // 2. Perform Full-Text Search
        $response = $this->getJson('/api/v1/search?index=inventories&q=airport');
        $this->assertApiResponse($response, 200);

        // Assert 2 hits returned (inv1 and inv3 contain 'airport', inv2 does not)
        $data = $response->json('data');
        $this->assertEquals(2, $data['meta']['total']);
        $hitIds = collect($data['hits'])->pluck('entity_id')->toArray();
        $this->assertContains($inv1->id, $hitIds);
        $this->assertContains($inv3->id, $hitIds);
        $this->assertNotContains($inv2->id, $hitIds);

        // 3. Search with Filter
        $response = $this->getJson('/api/v1/search?index=inventories&q=airport&filters[status]=approved');
        $this->assertApiResponse($response, 200);
        $data = $response->json('data');
        $this->assertEquals(1, $data['meta']['total']);
        $this->assertEquals($inv1->id, $data['hits'][0]['entity_id']);

        // 4. Request Facets
        $response = $this->getJson('/api/v1/search?index=inventories&q=airport&facets=status,inventory_category');
        $this->assertApiResponse($response, 200);
        $data = $response->json('data');
        
        $this->assertArrayHasKey('status', $data['facets']);
        $statusFacets = collect($data['facets']['status']['values']);
        $this->assertEquals(1, $statusFacets->where('value', 'approved')->first()['count']);
        $this->assertEquals(1, $statusFacets->where('value', 'draft')->first()['count']);

        // 5. Test Auto-Complete Suggestions
        $response = $this->getJson('/api/v1/search/suggest?index=inventories&q=met');
        $this->assertApiResponse($response, 200);
        $suggestions = $response->json('data.suggestions');
        $this->assertContains('Metro Billboard Near Airport', $suggestions);
    }

    public function test_global_cross_index_search(): void
    {
        $this->actingAs($this->admin);

        // 1. Create and index an Inventory
        $inv = Inventory::create([
            'id' => (string) Str::uuid(),
            'inventory_code' => 'INV-X99',
            'display_name' => 'Airport Highway Billboard',
            'provider_id' => $this->provider->id,
            'branch_id' => $this->branch->id,
            'country_id' => $this->country->id,
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'city_id' => $this->city->id,
            'pincode_id' => $this->pincode->id,
            'inventory_category' => 'billboard',
            'inventory_type' => 'digital',
            'ownership_type' => OwnershipType::Owned,
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'geo_hash' => 'tdr123',
            'normalized_address' => 'Airport Highway Bengaluru',
            'search_keywords' => 'highway billboard',
            'status' => InventoryStatus::Approved,
            'ai_scores' => new \App\Modules\Inventory\Domain\ValueObjects\InventoryScore(),
            'inventory_capabilities' => new \App\Modules\Inventory\Domain\ValueObjects\InventoryCapabilities(),
        ]);
        $this->indexingService->index($inv);

        // 2. Create and index a Booking
        $customer = User::factory()->create();
        $booking = Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => 'BK-AIRPORT-CAMPAIGN',
            'customer_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'subtotal_cents' => 400000,
            'tax_cents' => 20000,
            'grand_total_cents' => 420000,
            'currency' => 'INR',
            'status' => BookingStatus::Draft,
        ]);
        $this->indexingService->index($booking);

        // 3. Create and index an Invoice
        $invoice = Invoice::create([
            'id' => (string) Str::uuid(),
            'invoice_number' => 'INV-AIRPORT-DEAL',
            'booking_id' => $booking->id,
            'customer_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal_cents' => 400000,
            'tax_cents' => 20000,
            'grand_total_cents' => 420000,
            'currency' => 'INR',
            'status' => InvoiceStatus::Draft,
            'invoice_type' => InvoiceType::TaxInvoice,
            'booking_snapshot' => [],
        ]);
        $this->indexingService->index($invoice);

        // 4. Run Global Search
        $response = $this->getJson('/api/v1/search/global?q=AIRPORT');
        $this->assertApiResponse($response, 200);

        $results = collect($response->json('data.results'));
        
        // Assert items from all 3 different indexes are found in the merged global search results
        $this->assertTrue($results->contains('index', 'inventories'));
        $this->assertTrue($results->contains('index', 'bookings'));
        $this->assertTrue($results->contains('index', 'finance_invoices'));
    }

    public function test_saved_searches_crud(): void
    {
        $this->actingAs($this->admin);

        $queryPayload = [
            'term' => 'billboard',
            'index_name' => 'inventories',
            'filters' => ['status' => 'approved'],
            'facets' => ['city_id'],
            'sort_field' => 'created_at',
            'sort_direction' => 'desc',
        ];

        // 1. Create saved search
        $response = $this->postJson('/api/v1/search/saved', [
            'name' => 'My Favorite Billboards',
            'index_name' => 'inventories',
            'query_payload' => $queryPayload,
            'is_pinned' => true,
        ]);

        $this->assertApiResponse($response, 201);
        $savedId = $response->json('data.id');
        $this->assertNotNull($savedId);

        // 2. List saved searches
        $response = $this->getJson('/api/v1/search/saved');
        $this->assertApiResponse($response, 200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('My Favorite Billboards', $response->json('data.0.name'));
        $this->assertTrue($response->json('data.0.is_pinned'));

        // 3. Delete saved search
        $response = $this->deleteJson("/api/v1/search/saved/{$savedId}");
        $this->assertApiResponse($response, 200);

        // 4. Verify deleted
        $response = $this->getJson('/api/v1/search/saved');
        $this->assertCount(0, $response->json('data'));
    }
}
