<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderSubscription;
use App\Platform\Shared\Domain\Entities\City;
use App\Platform\Shared\Domain\Entities\Country;
use App\Platform\Shared\Domain\Entities\District;
use App\Platform\Shared\Domain\Entities\Pincode;
use App\Platform\Shared\Domain\Entities\State;
use Database\Seeders\GeographySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class InventoryApiTest extends ApiTestCase
{
    protected string $providerId;
    protected string $branchId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(GeographySeeder::class);

        // Create a branch
        $branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Guntur HQ',
            'code' => 'GNT-HQ',
            'support_email' => 'guntur@sodars.com',
            'support_phone' => '+91863123456',
        ]);
        $this->branchId = $branch->id;

        // Create a verified provider
        $provider = Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Test Media Corp',
            'registration_number' => 'REG-INV-TEST-001',
            'provider_code' => 'PRV-TEST-001',
            'default_branch_id' => $branch->id,
            'status' => 'verified',
            'preferred_payout_method' => 'bank',
        ]);
        $this->providerId = $provider->id;

        // Create active subscription
        ProviderSubscription::create([
            'id' => (string) Str::uuid(),
            'provider_id' => $provider->id,
            'max_active_screens' => 10,
            'billing_cycle' => 'monthly',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ]);
    }

    /**
     * Test inventory creation via API.
     */
    public function test_admin_can_create_inventory(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/v1/inventories', [
            'display_name' => 'Test Billboard Alpha',
            'provider_id' => $this->providerId,
            'inventory_category' => 'Static',
            'inventory_type' => 'Billboard',
            'ownership_type' => 'owned',
            'latitude' => 17.4401,
            'longitude' => 78.3489,
            'normalized_address' => 'Test Address, Hyderabad, Telangana 500081',
            'search_keywords' => 'test billboard alpha',
        ]);

        $this->assertApiResponse($response, 201);
        $response->assertJsonPath('data.display_name', 'Test Billboard Alpha');
        $response->assertJsonPath('data.status', 'draft');
        $this->assertNotNull($response->json('data.inventory_code'));

        // Assert database records
        $this->assertDatabaseHas('inventories', [
            'display_name' => 'Test Billboard Alpha',
            'provider_id' => $this->providerId,
        ]);

        // Assert default face was auto-created
        $inventory = Inventory::where('display_name', 'Test Billboard Alpha')->firstOrFail();
        $this->assertGreaterThanOrEqual(1, $inventory->faces()->count());
    }

    /**
     * Test inventory creation validation.
     */
    public function test_inventory_creation_validation(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/v1/inventories', [
            // Missing required fields
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['display_name', 'provider_id', 'inventory_category', 'inventory_type', 'ownership_type', 'latitude', 'longitude', 'normalized_address']);
    }

    /**
     * Test admin can list inventories.
     */
    public function test_admin_can_list_inventories(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/v1/inventories');

        $this->assertApiResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => ['id', 'inventory_code', 'display_name', 'status'],
                ],
            ],
        ]);
    }

    /**
     * Test inventory detail view with loaded graph.
     */
    public function test_admin_can_view_inventory_details(): void
    {
        $this->actingAsAdmin();

        // Create first
        $createResponse = $this->postJson('/api/v1/inventories', [
            'display_name' => 'Detail View Billboard',
            'provider_id' => $this->providerId,
            'inventory_category' => 'Static',
            'inventory_type' => 'Billboard',
            'ownership_type' => 'owned',
            'latitude' => 17.4401,
            'longitude' => 78.3489,
            'normalized_address' => 'Detail Test, Hyderabad',
        ]);

        $inventoryId = $createResponse->json('data.id');

        $response = $this->getJson("/api/v1/inventories/{$inventoryId}");

        $this->assertApiResponse($response, 200);
        $response->assertJsonPath('data.id', $inventoryId);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'inventory_code',
                'display_name',
                'faces',
            ],
        ]);
    }

    /**
     * Test inventory update.
     */
    public function test_admin_can_update_inventory(): void
    {
        $this->actingAsAdmin();

        $createResponse = $this->postJson('/api/v1/inventories', [
            'display_name' => 'Update Test Billboard',
            'provider_id' => $this->providerId,
            'inventory_category' => 'Static',
            'inventory_type' => 'Billboard',
            'ownership_type' => 'owned',
            'latitude' => 17.4401,
            'longitude' => 78.3489,
            'normalized_address' => 'Update Test, Hyderabad',
        ]);

        $inventoryId = $createResponse->json('data.id');

        $response = $this->putJson("/api/v1/inventories/{$inventoryId}", [
            'display_name' => 'Updated Billboard Name',
            'marketplace_enabled' => false,
        ]);

        $this->assertApiResponse($response, 200);
        $response->assertJsonPath('data.display_name', 'Updated Billboard Name');
    }

    /**
     * Test status transition: draft → pending_approval.
     */
    public function test_status_transition_draft_to_pending_approval(): void
    {
        $this->actingAsAdmin();

        $createResponse = $this->postJson('/api/v1/inventories', [
            'display_name' => 'Status Test Billboard',
            'provider_id' => $this->providerId,
            'inventory_category' => 'Static',
            'inventory_type' => 'Billboard',
            'ownership_type' => 'owned',
            'latitude' => 17.4401,
            'longitude' => 78.3489,
            'normalized_address' => 'Status Test, Hyderabad',
        ]);

        $inventoryId = $createResponse->json('data.id');

        $response = $this->patchJson("/api/v1/inventories/{$inventoryId}/status", [
            'status' => 'pending_approval',
        ]);

        $this->assertApiResponse($response, 200);
        $response->assertJsonPath('data.status', 'pending_approval');
    }

    /**
     * Test invalid state transition is rejected.
     */
    public function test_invalid_status_transition_rejected(): void
    {
        $this->actingAsAdmin();

        $createResponse = $this->postJson('/api/v1/inventories', [
            'display_name' => 'Invalid State Billboard',
            'provider_id' => $this->providerId,
            'inventory_category' => 'Static',
            'inventory_type' => 'Billboard',
            'ownership_type' => 'owned',
            'latitude' => 17.4401,
            'longitude' => 78.3489,
            'normalized_address' => 'Invalid State Test, Hyderabad',
        ]);

        $inventoryId = $createResponse->json('data.id');

        // Draft → Suspended is not a valid transition
        $response = $this->patchJson("/api/v1/inventories/{$inventoryId}/status", [
            'status' => 'suspended',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test adding a face to inventory.
     */
    public function test_admin_can_add_face(): void
    {
        $this->actingAsAdmin();

        $createResponse = $this->postJson('/api/v1/inventories', [
            'display_name' => 'Face Test Billboard',
            'provider_id' => $this->providerId,
            'inventory_category' => 'Static',
            'inventory_type' => 'Billboard',
            'ownership_type' => 'owned',
            'latitude' => 17.4401,
            'longitude' => 78.3489,
            'normalized_address' => 'Face Test, Hyderabad',
        ]);

        $inventoryId = $createResponse->json('data.id');

        $response = $this->postJson("/api/v1/inventories/{$inventoryId}/faces", [
            'face_code' => 'TEST-F2',
            'display_name' => 'Rear Face',
            'facing_direction' => 'south',
            'display_order' => 2,
            'physical_specifications' => [
                'width_cm' => 1200,
                'height_cm' => 600,
                'orientation' => 'landscape',
                'illuminated' => false,
            ],
        ]);

        $this->assertApiResponse($response, 201);
        $response->assertJsonPath('data.display_name', 'Rear Face');
        $response->assertJsonPath('data.facing_direction', 'south');
    }

    /**
     * Test adding pricing to a face.
     */
    public function test_admin_can_add_pricing_to_face(): void
    {
        $this->actingAsAdmin();

        $createResponse = $this->postJson('/api/v1/inventories', [
            'display_name' => 'Pricing Test Billboard',
            'provider_id' => $this->providerId,
            'inventory_category' => 'Static',
            'inventory_type' => 'Billboard',
            'ownership_type' => 'owned',
            'latitude' => 17.4401,
            'longitude' => 78.3489,
            'normalized_address' => 'Pricing Test, Hyderabad',
        ]);

        $inventoryId = $createResponse->json('data.id');
        $face = InventoryFace::where('inventory_id', $inventoryId)->firstOrFail();

        $response = $this->postJson("/api/v1/inventories/{$inventoryId}/faces/{$face->id}/pricing", [
            'pricing_type' => 'seasonal',
            'rate_cents' => 95000,
            'currency' => 'INR',
            'tax_inclusive' => false,
            'minimum_booking_days' => 7,
            'effective_from' => now()->addMonths(3)->toDateString(),
            'effective_to' => now()->addMonths(4)->toDateString(),
            'priority' => 10,
        ]);

        $this->assertApiResponse($response, 201);
        $response->assertJsonPath('data.pricing_type', 'seasonal');
        $response->assertJsonPath('data.rate_cents', 95000);
    }

    /**
     * Test blocking and unblocking availability.
     */
    public function test_admin_can_block_and_unblock_availability(): void
    {
        $this->actingAsAdmin();

        $createResponse = $this->postJson('/api/v1/inventories', [
            'display_name' => 'Availability Test Billboard',
            'provider_id' => $this->providerId,
            'inventory_category' => 'Static',
            'inventory_type' => 'Billboard',
            'ownership_type' => 'owned',
            'latitude' => 17.4401,
            'longitude' => 78.3489,
            'normalized_address' => 'Availability Test, Hyderabad',
        ]);

        $inventoryId = $createResponse->json('data.id');
        $face = InventoryFace::where('inventory_id', $inventoryId)->firstOrFail();

        // Block
        $blockResponse = $this->postJson("/api/v1/inventories/{$inventoryId}/faces/{$face->id}/availability", [
            'start_at' => now()->addDays(60)->toDateTimeString(),
            'end_at' => now()->addDays(67)->toDateTimeString(),
            'availability_status' => 'maintenance',
            'reason' => 'Scheduled maintenance',
            'remarks' => 'Electrical panel replacement.',
        ]);

        $this->assertApiResponse($blockResponse, 201);
        $availabilityId = $blockResponse->json('data.id');

        // List availability
        $listResponse = $this->getJson("/api/v1/inventories/{$inventoryId}/faces/{$face->id}/availability");
        $this->assertApiResponse($listResponse, 200);

        // Unblock
        $unblockResponse = $this->deleteJson("/api/v1/inventories/{$inventoryId}/faces/{$face->id}/availability/{$availabilityId}");
        $this->assertApiResponse($unblockResponse, 200);
    }

    /**
     * Test document upload.
     */
    public function test_admin_can_upload_document(): void
    {
        $this->actingAsAdmin();

        $createResponse = $this->postJson('/api/v1/inventories', [
            'display_name' => 'Document Test Billboard',
            'provider_id' => $this->providerId,
            'inventory_category' => 'Static',
            'inventory_type' => 'Billboard',
            'ownership_type' => 'owned',
            'latitude' => 17.4401,
            'longitude' => 78.3489,
            'normalized_address' => 'Document Test, Hyderabad',
        ]);

        $inventoryId = $createResponse->json('data.id');

        $response = $this->postJson("/api/v1/inventories/{$inventoryId}/documents", [
            'document_type' => 'municipal_permit',
            'file_path' => 'uploads/inventory/documents/permit.pdf',
        ]);

        $this->assertApiResponse($response, 201);
        $this->assertDatabaseHas('inventory_documents', [
            'inventory_id' => $inventoryId,
            'document_type' => 'municipal_permit',
        ]);
    }

    /**
     * Test dashboard endpoint.
     */
    public function test_admin_can_view_dashboard(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/v1/inventories/dashboard');

        $this->assertApiResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'total_structures',
                'total_faces',
                'active_faces',
                'marketplace_enabled_count',
                'occupancy_rate',
            ],
        ]);
    }

    /**
     * Test search endpoint.
     */
    public function test_search_endpoint(): void
    {
        $this->actingAsAdmin();

        // Create inventory to search for
        $this->postJson('/api/v1/inventories', [
            'display_name' => 'Searchable Billboard',
            'provider_id' => $this->providerId,
            'inventory_category' => 'Digital',
            'inventory_type' => 'LED Screen',
            'ownership_type' => 'leased',
            'latitude' => 17.4401,
            'longitude' => 78.3489,
            'normalized_address' => 'Search Test, Hyderabad',
            'search_keywords' => 'searchable led digital',
        ]);

        $response = $this->getJson('/api/v1/inventories/search?search=searchable');

        $this->assertApiResponse($response, 200);
    }

    /**
     * Test unauthenticated access is blocked.
     */
    public function test_unauthenticated_access_is_blocked(): void
    {
        $response = $this->getJson('/api/v1/inventories');

        $response->assertStatus(401);
    }
}
