<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Platform\Identity\Domain\Entities\Organization;
use App\Platform\Identity\Domain\Entities\OrganizationMember;
use App\Platform\Identity\Domain\Entities\Team;
use App\Platform\Identity\Domain\Entities\LoginSession;
use App\Platform\Identity\Domain\Entities\ActivityLog;
use App\Platform\Identity\Domain\Contracts\PermissionResolver;
use App\Platform\Identity\Application\Services\IdentityContext;
use App\Platform\Identity\Application\Services\SessionService;
use App\Platform\Identity\Application\Services\ActivityService;
use App\Platform\Identity\Domain\Enums\ActivityType;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class IdentityApiTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user1;
    protected User $user2;
    protected Organization $org1;
    protected Organization $org2;

    protected function setUp(): void
    {
        parent::setUp();
        IdentityContext::clear();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();

        // Create two organizations
        $this->org1 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant One',
            'slug' => 'tenant-one',
            'is_active' => true,
        ]);

        $this->org2 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant Two',
            'slug' => 'tenant-two',
            'is_active' => true,
        ]);

        // Associate user1 with org1, user2 with org2
        OrganizationMember::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->org1->id,
            'user_id' => $this->user1->id,
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);
        $this->user1->update(['organization_id' => $this->org1->id]);

        OrganizationMember::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->org2->id,
            'user_id' => $this->user2->id,
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);
        $this->user2->update(['organization_id' => $this->org2->id]);
    }

    // ─────────────────────────────────────────────────────
    // 1. Organization CRUD & Membership
    // ─────────────────────────────────────────────────────

    public function test_organization_crud_and_membership(): void
    {
        $this->actingAs($this->admin);

        // Create organization
        $response = $this->postJson('/api/v1/organizations', [
            'name' => 'New Org',
            'slug' => 'new-org',
        ]);
        $response->assertStatus(201);
        $orgId = $response->json('data.id');

        $this->assertDatabaseHas('organizations', [
            'id' => $orgId,
            'name' => 'New Org',
        ]);

        // Get members
        $response = $this->getJson("/api/v1/organizations/{$orgId}/members");
        $response->assertStatus(200);

        // Add member
        $newUser = User::factory()->create();
        $response = $this->postJson("/api/v1/organizations/{$orgId}/members", [
            'user_id' => $newUser->id,
            'role' => 'member',
        ]);
        $response->assertStatus(201);

        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $orgId,
            'user_id' => $newUser->id,
            'role' => 'member',
        ]);

        // Remove member
        $response = $this->deleteJson("/api/v1/organizations/{$orgId}/members/{$newUser->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('organization_members', [
            'organization_id' => $orgId,
            'user_id' => $newUser->id,
        ]);
    }

    // ─────────────────────────────────────────────────────
    // 2. Multi-Tenant Isolation / Row-Level Security
    // ─────────────────────────────────────────────────────

    public function test_multi_tenant_isolation(): void
    {
        // Set context as user1 (Tenant One)
        $this->actingAs($this->user1);

        // Create a team under Tenant One context (EnforceOrganizationScope active)
        $response = $this->postJson('/api/v1/teams', [
            'name' => 'Team Alpha',
            'description' => 'Alpha desc',
        ]);
        $response->assertStatus(201);
        $teamId = $response->json('data.id');

        // Team should have tenant org1 set automatically
        $this->assertDatabaseHas('teams', [
            'id' => $teamId,
            'organization_id' => $this->org1->id,
            'name' => 'Team Alpha',
        ]);

        // Accessing teams from Tenant One should return Team Alpha
        $response = $this->getJson('/api/v1/teams');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('Team Alpha', $response->json('data.0.name'));

        // Accessing Tenant One team from Tenant Two user should fail or not list it
        $this->actingAs($this->user2);

        // Listing teams as user2 should return empty (since they have no teams in Tenant Two)
        $response = $this->getJson('/api/v1/teams');
        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');

        // Direct show endpoint for org1 team by org2 user should be blocked (returns 404 because of global scoping)
        $response = $this->getJson("/api/v1/teams/{$teamId}");
        $response->assertStatus(404);
    }

    // ─────────────────────────────────────────────────────
    // 3. Team CRUD & Validation
    // ─────────────────────────────────────────────────────

    public function test_team_membership_requires_organization_membership(): void
    {
        $this->actingAs($this->user1);

        // Create team
        $response = $this->postJson('/api/v1/teams', [
            'name' => 'Product Team',
        ]);
        $response->assertStatus(201);
        $teamId = $response->json('data.id');

        // Trying to add user2 (who is in Tenant Two) to Tenant One team should fail
        $response = $this->postJson("/api/v1/teams/{$teamId}/members", [
            'user_id' => $this->user2->id,
            'role' => 'member',
        ]);
        $response->assertStatus(400)
            ->assertJsonFragment(['message' => 'User must be an organization member before joining a team.']);

        // Add a tenant member (newUser in Org1) to team
        $newUser = User::factory()->create();
        OrganizationMember::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->org1->id,
            'user_id' => $newUser->id,
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->postJson("/api/v1/teams/{$teamId}/members", [
            'user_id' => $newUser->id,
            'role' => 'member',
        ]);
        $response->assertStatus(201);

        $this->assertDatabaseHas('team_members', [
            'team_id' => $teamId,
            'user_id' => $newUser->id,
        ]);
    }

    // ─────────────────────────────────────────────────────
    // 4. Session Tracking & Revocation
    // ─────────────────────────────────────────────────────

    public function test_login_session_lifecycle(): void
    {
        // Fire Laravel Login event
        event(new Login('web', $this->user1, false));

        // Verify session was logged in DB
        $this->assertDatabaseHas('login_sessions', [
            'user_id' => $this->user1->id,
            'is_revoked' => false,
        ]);

        $session = LoginSession::where('user_id', $this->user1->id)->first();
        $this->assertNotNull($session);

        // Simulate request using context
        $this->actingAs($this->user1);

        // List sessions
        $response = $this->getJson('/api/v1/sessions');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');

        // Revoke session
        $response = $this->deleteJson("/api/v1/sessions/{$session->id}");
        $response->assertStatus(200);

        $session->refresh();
        $this->assertTrue($session->is_revoked);
        $this->assertNotNull($session->logged_out_at);
    }

    // ─────────────────────────────────────────────────────
    // 5. Immutable Activity Log queries
    // ─────────────────────────────────────────────────────

    public function test_activity_logging_and_queries(): void
    {
        $this->actingAs($this->user1);
        IdentityContext::initFromAuth();

        // Record some dummy activity
        ActivityService::record(
            ActivityType::ProfileUpdated,
            'Updated avatar image',
            $this->user1
        );

        $this->assertDatabaseHas('identity_activity_logs', [
            'user_id' => $this->user1->id,
            'activity_type' => 'profile_updated',
            'description' => 'Updated avatar image',
        ]);

        // Query activities
        $response = $this->getJson('/api/v1/activity');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['data']]);
    }

    // ─────────────────────────────────────────────────────
    // 6. PermissionResolver Contract Verification
    // ─────────────────────────────────────────────────────

    public function test_permission_resolver_gateways(): void
    {
        $resolver = $this->app->make(PermissionResolver::class);

        // Super admin should resolve true
        $this->assertTrue($resolver->hasPermission($this->admin->id, 'settings.view'));

        // Normal user without roles should resolve false
        $this->assertFalse($resolver->hasPermission($this->user1->id, 'settings.view'));

        // Assigning role gives access
        $this->user1->assignRole('branch_manager');
        $this->assertTrue($resolver->hasPermission($this->user1->id, 'branch.view'));
    }
}
