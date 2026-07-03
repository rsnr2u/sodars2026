<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignCreative;
use App\Modules\Campaigns\Domain\Entities\CampaignProof;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderSubscription;
use Database\Seeders\GeographySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class CampaignApiTest extends ApiTestCase
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
            'name' => 'HQ Branch',
            'code' => 'HQ-B',
            'support_email' => 'hq@sodars.com',
            'support_phone' => '+91800100200',
        ]);
        $this->branchId = $branch->id;

        // Provider
        $provider = Provider::create([
            'id' => (string) Str::uuid(),
            'company_name' => 'Ad Provider',
            'registration_number' => 'REG-CMP-PRV',
            'provider_code' => 'PRV-CMP-01',
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

        // Inventory Face
        $inventory = Inventory::create([
            'id' => (string) Str::uuid(),
            'inventory_code' => 'INV-CMP-001',
            'display_name' => 'Billboard City',
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
            'geo_hash' => 'te7u61gf',
            'normalized_address' => 'Hitec City, Hyderabad',
            'status' => 'approved',
            'ai_scores' => [
                'visibility_score' => 80,
                'traffic_score' => 80,
                'engagement_score' => 80,
                'overall_score' => 80
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

        $face = InventoryFace::create([
            'id' => (string) Str::uuid(),
            'inventory_id' => $inventory->id,
            'face_code' => 'INV-CMP-001-F1',
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
    }

    public function test_admin_can_create_campaign(): void
    {
        $admin = $this->actingAsAdmin();

        $response = $this->postJson('/api/v1/campaigns', [
            'name' => 'New Product Launch',
            'customer_id' => $admin->id,
            'branch_id' => $this->branchId,
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'budget_cents' => 3000000,
            'inventory_face_ids' => [$this->faceId],
        ]);

        $this->assertApiResponse($response, 201);
        $response->assertJsonPath('data.name', 'New Product Launch');
        $response->assertJsonPath('data.status', 'draft');
        $this->assertNotNull($response->json('data.campaign_code'));
    }

    public function test_campaign_input_validation(): void
    {
        $this->actingAsAdmin();

        // Past dates must trigger validation error
        $response = $this->postJson('/api/v1/campaigns', [
            'name' => 'Invalid Campaign',
            'customer_id' => (string) Str::uuid(),
            'branch_id' => $this->branchId,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->subDays(1)->toDateString(),
        ]);

        $response->assertStatus(422);
    }

    public function test_upload_creative_and_audit_flow(): void
    {
        $admin = $this->actingAsAdmin();

        $createResponse = $this->postJson('/api/v1/campaigns', [
            'name' => 'Artwork Audit Campaign',
            'customer_id' => $admin->id,
            'branch_id' => $this->branchId,
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'inventory_face_ids' => [$this->faceId],
        ]);

        $campaignId = $createResponse->json('data.id');

        // Upload creative
        $uploadResponse = $this->postJson("/api/v1/campaigns/{$campaignId}/creatives", [
            'file_path' => 'uploads/campaigns/brand_art.png',
            'file_name' => 'brand_art.png',
            'file_type' => 'png',
        ]);

        $this->assertApiResponse($uploadResponse, 201);
        $creativeId = $uploadResponse->json('data.id');

        // Audit creative (rejection)
        $auditReject = $this->patchJson("/api/v1/campaigns/{$campaignId}/creatives/{$creativeId}/audit", [
            'status' => 'rejected',
            'rejection_reason' => 'Offensive text found.',
        ]);

        $this->assertApiResponse($auditReject, 200);
        $this->assertDatabaseHas('campaign_creatives', [
            'id' => $creativeId,
            'status' => 'rejected',
        ]);

        // Audit creative (approval)
        $auditApprove = $this->patchJson("/api/v1/campaigns/{$campaignId}/creatives/{$creativeId}/audit", [
            'status' => 'approved',
        ]);

        $this->assertApiResponse($auditApprove, 200);
        $this->assertDatabaseHas('campaign_creatives', [
            'id' => $creativeId,
            'status' => 'approved',
        ]);

        // Eager load schedule grid generation verification
        $this->assertDatabaseHas('campaign_schedule', [
            'campaign_id' => $campaignId,
            'inventory_face_id' => $this->faceId,
        ]);
    }

    public function test_upload_and_verify_execution_proof(): void
    {
        $admin = $this->actingAsAdmin();

        $createResponse = $this->postJson('/api/v1/campaigns', [
            'name' => 'Execution Verification Campaign',
            'customer_id' => $admin->id,
            'branch_id' => $this->branchId,
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'inventory_face_ids' => [$this->faceId],
        ]);

        $campaignId = $createResponse->json('data.id');

        // Upload proof
        $proofResponse = $this->postJson("/api/v1/campaigns/{$campaignId}/proofs", [
            'file_path' => 'uploads/campaigns/proof_1.jpg',
            'inventory_face_id' => $this->faceId,
            'notes' => 'Campaign displayed successfully.',
        ]);

        $this->assertApiResponse($proofResponse, 201);
        $proofId = $proofResponse->json('data.id');

        // Audit proof
        $auditResponse = $this->patchJson("/api/v1/campaigns/{$campaignId}/proofs/{$proofId}/audit", [
            'status' => 'verified',
        ]);

        $this->assertApiResponse($auditResponse, 200);
        $this->assertDatabaseHas('campaign_proofs', [
            'id' => $proofId,
            'status' => 'verified',
        ]);
    }

    public function test_view_dashboard_metrics(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/v1/campaigns/dashboard');

        $this->assertApiResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'total_campaigns',
                'running_campaigns',
                'paused_campaigns',
                'pending_creatives',
                'total_budget_cents',
                'currency',
            ],
        ]);
    }
}
