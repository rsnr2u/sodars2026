<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderBankAccount;
use App\Modules\Providers\Domain\Entities\ProviderDocument;
use App\Modules\Providers\Domain\Entities\ProviderStaff;
use App\Platform\Shared\Domain\Entities\City;
use App\Platform\Shared\Domain\Entities\Country;
use App\Platform\Shared\Domain\Entities\District;
use App\Platform\Shared\Domain\Entities\State;
use Database\Seeders\GeographySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class ProviderApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(GeographySeeder::class);

        // Ensure at least one branch exists
        Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Guntur HQ',
            'code' => 'GNT-HQ',
            'support_email' => 'guntur@sodars.com',
            'support_phone' => '+91863123456',
        ]);
    }

    /**
     * Test public provider registration.
     */
    public function test_public_provider_registration(): void
    {
        $response = $this->postJson('/api/v1/providers', [
            'company_name' => 'Alpha Media Group',
            'registration_number' => 'REG-999888777',
            'city' => 'Guntur',
            'state' => 'Andhra Pradesh',
            'contact_name' => 'David Miller',
            'email' => 'david.miller@alpha.com',
            'phone' => '+919999988888',
            'password' => 'securePassword123',
        ]);

        $this->assertApiResponse($response, 201);
        $response->assertJsonPath('data.company_name', 'Alpha Media Group');
        $response->assertJsonPath('data.status', 'draft');
        $this->assertNotNull($response->json('data.provider_code'));

        // Assert database records
        $this->assertDatabaseHas('providers', [
            'company_name' => 'Alpha Media Group',
            'registration_number' => 'REG-999888777',
        ]);

        $this->assertDatabaseHas('provider_contacts', [
            'contact_name' => 'David Miller',
            'email' => 'david.miller@alpha.com',
        ]);
    }

    /**
     * Test duplicate registration numbers are blocked.
     */
    public function test_duplicate_registration_number_validation(): void
    {
        // First registration
        $this->postJson('/api/v1/providers', [
            'company_name' => 'Alpha Media Group',
            'registration_number' => 'REG-999888777',
            'city' => 'Guntur',
            'state' => 'Andhra Pradesh',
            'contact_name' => 'David Miller',
            'email' => 'david.miller@alpha.com',
            'phone' => '+919999988888',
            'password' => 'securePassword123',
        ]);

        // Duplicate registration
        $response = $this->postJson('/api/v1/providers', [
            'company_name' => 'Beta Media Group',
            'registration_number' => 'REG-999888777',
            'city' => 'Guntur',
            'state' => 'Andhra Pradesh',
            'contact_name' => 'Sarah Connor',
            'email' => 'sarah.connor@beta.com',
            'phone' => '+919999988889',
            'password' => 'securePassword123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['registration_number']);
    }

    /**
     * Test list providers for super admin.
     */
    public function test_admin_can_list_providers(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/v1/providers');

        $this->assertApiResponse($response, 200);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => ['id', 'company_name', 'registration_number', 'provider_code', 'status'],
                ],
            ],
        ]);
    }

    /**
     * Test compliance document upload and status verification auditing flow.
     */
    public function test_document_upload_and_manager_audit_flow(): void
    {
        // 1. Create a draft provider
        $this->postJson('/api/v1/providers', [
            'company_name' => 'Audited Media Corp',
            'registration_number' => 'REG-111222333',
            'city' => 'Guntur',
            'state' => 'Andhra Pradesh',
            'contact_name' => 'Audit Admin',
            'email' => 'admin@auditcorp.com',
            'phone' => '+919999977777',
            'password' => 'securePassword123',
        ]);

        $provider = Provider::where('registration_number', 'REG-111222333')->firstOrFail();

        // Acting as admin/manager for updates
        $this->actingAsAdmin();

        // 2. Upload document (changes provider status to pending)
        $uploadResponse = $this->postJson("/api/v1/providers/{$provider->id}/documents", [
            'document_type' => 'business_registry',
            'file_path' => 'uploads/test/business_registry.pdf',
        ]);

        $this->assertApiResponse($uploadResponse, 201);
        $this->assertDatabaseHas('provider_documents', [
            'provider_id' => $provider->id,
            'document_type' => 'business_registry',
            'status' => 'pending',
            'is_current' => true,
        ]);

        $this->assertDatabaseHas('providers', [
            'id' => $provider->id,
            'status' => 'pending',
        ]);

        $docId = $uploadResponse->json('data.id');

        // 3. Audit document (approves document and transitions provider to verified status)
        $auditResponse = $this->patchJson("/api/v1/providers/{$provider->id}/documents/{$docId}/audit", [
            'status' => 'approved',
            'remarks' => 'Verification details match registry record.',
        ]);

        $this->assertApiResponse($auditResponse, 200);
        $this->assertDatabaseHas('provider_documents', [
            'id' => $docId,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('providers', [
            'id' => $provider->id,
            'status' => 'verified',
        ]);
    }

    /**
     * Test configuring bank accounts.
     */
    public function test_payout_bank_account_configuration(): void
    {
        $this->postJson('/api/v1/providers', [
            'company_name' => 'Banked Corp',
            'registration_number' => 'REG-444555666',
            'city' => 'Guntur',
            'state' => 'Andhra Pradesh',
            'contact_name' => 'Bank Admin',
            'email' => 'admin@banked.com',
            'phone' => '+919999966666',
            'password' => 'securePassword123',
        ]);

        $provider = Provider::where('registration_number', 'REG-444555666')->firstOrFail();

        $this->actingAsAdmin();

        $response = $this->putJson("/api/v1/providers/{$provider->id}/bank-accounts", [
            'bank_name' => 'Royal Charter Bank',
            'account_holder' => 'Banked Corp LLC',
            'account_number' => '9876543210',
            'routing_code' => 'ROYB0002',
            'is_primary' => true,
        ]);

        $this->assertApiResponse($response, 200);
        $this->assertDatabaseHas('provider_bank_accounts', [
            'provider_id' => $provider->id,
            'bank_name' => 'Royal Charter Bank',
            'account_number' => '9876543210',
        ]);
    }

    /**
     * Test staff addition and removal.
     */
    public function test_staff_membership_management(): void
    {
        $this->postJson('/api/v1/providers', [
            'company_name' => 'Staffed Corp',
            'registration_number' => 'REG-777888999',
            'city' => 'Guntur',
            'state' => 'Andhra Pradesh',
            'contact_name' => 'Staff Admin',
            'email' => 'admin@staffed.com',
            'phone' => '+919999955555',
            'password' => 'securePassword123',
        ]);

        $provider = Provider::where('registration_number', 'REG-777888999')->firstOrFail();

        $this->actingAsAdmin();

        // Add staff
        $addResponse = $this->postJson("/api/v1/providers/{$provider->id}/staff", [
            'name' => 'Assistant Doe',
            'email' => 'assistant@staffed.com',
            'password' => 'securePassword1234',
        ]);

        $this->assertApiResponse($addResponse, 201);
        $staffId = $addResponse->json('data.id');

        $this->assertDatabaseHas('provider_staff', [
            'id' => $staffId,
            'is_active' => true,
        ]);

        // Remove staff
        $removeResponse = $this->deleteJson("/api/v1/providers/{$provider->id}/staff/{$staffId}");
        $this->assertApiResponse($removeResponse, 200);

        $this->assertDatabaseHas('provider_staff', [
            'id' => $staffId,
            'is_active' => false,
        ]);
    }

    /**
     * Test state machine validation.
     */
    public function test_state_machine_transition_validation(): void
    {
        $this->postJson('/api/v1/providers', [
            'company_name' => 'State Corp',
            'registration_number' => 'REG-888888888',
            'city' => 'Guntur',
            'state' => 'Andhra Pradesh',
            'contact_name' => 'State Admin',
            'email' => 'admin@statecorp.com',
            'phone' => '+919999944444',
            'password' => 'securePassword123',
        ]);

        $provider = Provider::where('registration_number', 'REG-888888888')->firstOrFail();

        $this->actingAsAdmin();

        // Invalid: Draft to Suspended (must go via pending -> verified first)
        $response = $this->patchJson("/api/v1/providers/{$provider->id}/status", [
            'status' => 'suspended',
        ]);

        $response->assertStatus(422);
    }
}
