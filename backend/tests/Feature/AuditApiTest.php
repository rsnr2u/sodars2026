<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Platform\Identity\Domain\Entities\Organization;
use App\Platform\Identity\Domain\Entities\OrganizationMember;
use App\Platform\Identity\Domain\Entities\Team;
use App\Platform\Audit\Domain\Entities\AuditEvent;
use App\Platform\Audit\Domain\Enums\EventCategory;
use App\Platform\Audit\Domain\Enums\RiskLevel;
use App\Platform\Identity\Domain\Events\UserLoggedIn;
use App\Platform\DAM\Application\Services\DAMService;
use App\Platform\DAM\Domain\Entities\Asset;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class AuditApiTest extends ApiTestCase
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
        \App\Core\Context\ContextManager::clear();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();

        // Create two organizations
        $this->org1 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Org One',
            'slug' => 'org-one',
            'is_active' => true,
        ]);

        $this->org2 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Org Two',
            'slug' => 'org-two',
            'is_active' => true,
        ]);

        // Mappings
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
    // 1. Automated Eloquent Auditing via Auditable Trait
    // ─────────────────────────────────────────────────────

    public function test_automated_eloquent_auditing_and_snapshots(): void
    {
        $this->actingAs($this->user1);
        \App\Core\Context\ContextManager::boot();

        // Create a Team (triggers created model event)
        $team = Team::create([
            'name' => 'Dev Team',
            'description' => 'Software engineering team',
            'is_active' => true,
        ]);

        // Assert audit log created
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'model.created',
            'auditable_type' => Team::class,
            'auditable_id' => $team->id,
            'organization_id' => $this->org1->id,
        ]);

        $createdEvent = AuditEvent::where('event_type', 'model.created')->first();
        $this->assertEquals('Dev Team', $createdEvent->after_snapshot['name']);
        // Description should be excluded due to public array $auditExclude in Team
        $this->assertArrayNotHasKey('description', $createdEvent->after_snapshot);

        // Update the Team (triggers updated model event)
        $team->update([
            'name' => 'Engineering Team',
            'description' => 'Updated description',
        ]);

        $updatedEvent = AuditEvent::where('event_type', 'model.updated')->first();
        $this->assertNotNull($updatedEvent);
        $this->assertEquals('Dev Team', $updatedEvent->before_snapshot['name']);
        $this->assertEquals('Engineering Team', $updatedEvent->after_snapshot['name']);
        $this->assertArrayNotHasKey('description', $updatedEvent->before_snapshot);
    }

    // ─────────────────────────────────────────────────────
    // 2. Event-Driven Auditing Mappings
    // ─────────────────────────────────────────────────────

    public function test_event_driven_compliance_auditing(): void
    {
        // Fire custom UserLoggedIn event
        event(new UserLoggedIn(
            (string) $this->user1->id,
            '192.168.1.1',
            'Mozilla/Firefox'
        ));

        // Registry should resolve category to Authentication and record it
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'user.login',
            'category' => 'authentication',
            'user_id' => $this->user1->id,
            'ip_address' => '192.168.1.1',
        ]);
    }

    // ─────────────────────────────────────────────────────
    // 3. Multi-Tenant Scoping and Security
    // ─────────────────────────────────────────────────────

    public function test_tenant_scoping_on_audit_logs(): void
    {
        // Seed logs manually under both orgs
        AuditEvent::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->org1->id,
            'category' => 'system',
            'event_type' => 'test.org1',
            'occurred_at' => now(),
            'description' => 'Org 1 log',
            'risk_level' => 'low',
        ]);

        AuditEvent::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->org2->id,
            'category' => 'system',
            'event_type' => 'test.org2',
            'occurred_at' => now(),
            'description' => 'Org 2 log',
            'risk_level' => 'low',
        ]);

        // User1 queries logs (Org 1)
        $this->actingAs($this->user1);
        $response = $this->getJson('/api/v1/compliance/audit');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $this->assertEquals('test.org1', $response->json('data.data.0.event_type'));

        // User2 queries logs (Org 2)
        $this->actingAs($this->user2);
        $response = $this->getJson('/api/v1/compliance/audit');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $this->assertEquals('test.org2', $response->json('data.data.0.event_type'));
    }

    // ─────────────────────────────────────────────────────
    // 4. DAM & Notification Compliance Export Integration
    // ─────────────────────────────────────────────────────

    public function test_compliance_logs_export(): void
    {
        $this->actingAs($this->user1);

        // Manually seed an audit event to export
        AuditEvent::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->org1->id,
            'category' => 'system',
            'event_type' => 'compliance.test',
            'occurred_at' => now(),
            'description' => 'Export test log',
            'risk_level' => 'low',
        ]);

        // Mock DAMService upload
        $this->app->bind(DAMService::class, function () {
            return new class {
                public function upload($file, $title, $description = null) {
                    $asset = new Asset();
                    $asset->id = (string) Str::uuid();
                    return $asset;
                }
                public function getUrl($path) {
                    return 'https://sodars.local/dam/file.csv';
                }
            };
        });

        // Hit export endpoint
        $response = $this->postJson('/api/v1/compliance/audit/export');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'asset_id',
                    'filename',
                    'download_url',
                ]
            ]);
    }
}
