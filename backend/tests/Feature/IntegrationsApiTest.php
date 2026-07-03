<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Platform\Integrations\Domain\ApiKeys\ApiKey;
use App\Platform\Integrations\Domain\Webhooks\WebhookSubscription;
use App\Platform\Integrations\Domain\Webhooks\WebhookDeliveryLog;
use App\Platform\Integrations\Domain\Contracts\WebhookSigner;
use App\Platform\Integrations\Domain\Contracts\WebhookTransport;
use App\Platform\Integrations\Application\Jobs\DeliverWebhookJob;
use App\Platform\Integrations\Infrastructure\Signers\HmacWebhookSigner;
use App\Platform\Integrations\Infrastructure\Registry\WebhookEventRegistry;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class IntegrationsApiTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');
    }

    // ─────────────────────────────────────────────────────
    // 1. API Key Lifecycle
    // ─────────────────────────────────────────────────────

    public function test_api_key_lifecycle(): void
    {
        $this->actingAs($this->admin);

        // Create a live API key
        $response = $this->postJson('/api/v1/integrations/keys', [
            'name' => 'My Integration Key',
            'scopes' => ['bookings:read', 'inventory:read'],
        ]);
        $response->assertStatus(201);
        $plainTextKey = $response->json('data.plain_text_key');
        $keyId = $response->json('data.api_key.id');

        // Plain text key starts with sodars_live_
        $this->assertStringStartsWith('sodars_live_', $plainTextKey);

        // Secret hash is stored (never the raw secret)
        $this->assertDatabaseHas('developer_api_keys', [
            'id' => $keyId,
            'name' => 'My Integration Key',
            'key_prefix' => 'sodars_live_',
        ]);

        // Verify scopes are stored
        $storedKey = ApiKey::find($keyId);
        $this->assertTrue($storedKey->hasScope('bookings:read'));
        $this->assertTrue($storedKey->hasScope('inventory:read'));
        $this->assertFalse($storedKey->hasScope('crm:write'));

        // List keys
        $response = $this->getJson('/api/v1/integrations/keys');
        $response->assertStatus(200);

        // Create a test API key
        $response = $this->postJson('/api/v1/integrations/keys', [
            'name' => 'Test Key',
            'is_test' => true,
        ]);
        $response->assertStatus(201);
        $testKey = $response->json('data.plain_text_key');
        $this->assertStringStartsWith('sodars_test_', $testKey);

        // Revoke the live key
        $response = $this->deleteJson("/api/v1/integrations/keys/{$keyId}");
        $response->assertStatus(200);

        $this->assertDatabaseHas('developer_api_keys', [
            'id' => $keyId,
            'is_active' => false,
        ]);
    }

    // ─────────────────────────────────────────────────────
    // 2. Public API Key Authentication
    // ─────────────────────────────────────────────────────

    public function test_public_api_key_authentication(): void
    {
        // Create an API key manually
        $secret = Str::random(32);
        $hash = hash('sha256', $secret);
        $plainTextKey = 'sodars_live_' . $secret;

        ApiKey::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->admin->id,
            'name' => 'Auth Test Key',
            'key_prefix' => 'sodars_live_',
            'secret_hash' => $hash,
            'scopes' => ['bookings:read'],
            'is_active' => true,
        ]);

        // Valid key should authenticate
        $response = $this->getJson('/api/public/v1/ping', [
            'X-API-KEY' => $plainTextKey,
        ]);
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'API Key authentication successful.']);

        // last_used_at should be updated
        $key = ApiKey::where('secret_hash', $hash)->first();
        $this->assertNotNull($key->last_used_at);

        // Missing key should return 401
        $response = $this->getJson('/api/public/v1/ping');
        $response->assertStatus(401);

        // Invalid key should return 401
        $response = $this->getJson('/api/public/v1/ping', [
            'X-API-KEY' => 'sodars_live_invalidkey12345',
        ]);
        $response->assertStatus(401);

        // Revoked key should return 401
        $key->update(['revoked_at' => now(), 'is_active' => false]);
        $response = $this->getJson('/api/public/v1/ping', [
            'X-API-KEY' => $plainTextKey,
        ]);
        $response->assertStatus(401);
    }

    // ─────────────────────────────────────────────────────
    // 3. Webhook Subscriptions CRUD
    // ─────────────────────────────────────────────────────

    public function test_webhook_subscription_crud(): void
    {
        $this->actingAs($this->admin);

        // Create subscription
        $response = $this->postJson('/api/v1/integrations/webhooks', [
            'target_url' => 'https://example.com/webhook',
            'event_types' => ['booking.created', 'invoice.created'],
        ]);
        $response->assertStatus(201);
        $subId = $response->json('data.id');

        $this->assertDatabaseHas('webhook_subscriptions', [
            'id' => $subId,
            'target_url' => 'https://example.com/webhook',
            'is_active' => true,
        ]);

        // secret_token is auto-generated
        $sub = WebhookSubscription::find($subId);
        $this->assertStringStartsWith('whsec_', $sub->secret_token);

        // List subscriptions
        $response = $this->getJson('/api/v1/integrations/webhooks');
        $response->assertStatus(200);

        // Invalid event type should fail
        $response = $this->postJson('/api/v1/integrations/webhooks', [
            'target_url' => 'https://example.com/webhook2',
            'event_types' => ['nonexistent.event'],
        ]);
        $response->assertStatus(400);

        // Deactivate
        $response = $this->deleteJson("/api/v1/integrations/webhooks/{$subId}");
        $response->assertStatus(200);
        $this->assertDatabaseHas('webhook_subscriptions', [
            'id' => $subId,
            'is_active' => false,
        ]);
    }

    // ─────────────────────────────────────────────────────
    // 4. HMAC Signature Verification
    // ─────────────────────────────────────────────────────

    public function test_hmac_signature_verification(): void
    {
        $signer = new HmacWebhookSigner();
        $secret = 'whsec_testSecret1234567890ab';
        $timestamp = time();
        $payload = '{"specversion":"1.0","type":"booking.created","data":{"id":"123"}}';

        $signature = $signer->sign($payload, $secret, $timestamp);

        // Signature is deterministic
        $signature2 = $signer->sign($payload, $secret, $timestamp);
        $this->assertEquals($signature, $signature2);

        // Different payload produces different signature
        $differentSig = $signer->sign('{"different":"payload"}', $secret, $timestamp);
        $this->assertNotEquals($signature, $differentSig);

        // Different secret produces different signature
        $differentSecretSig = $signer->sign($payload, 'whsec_differentSecret', $timestamp);
        $this->assertNotEquals($signature, $differentSecretSig);

        // Different timestamp produces different signature
        $differentTimeSig = $signer->sign($payload, $secret, $timestamp + 100);
        $this->assertNotEquals($signature, $differentTimeSig);
    }

    // ─────────────────────────────────────────────────────
    // 5. Webhook Delivery Job and Logging
    // ─────────────────────────────────────────────────────

    public function test_webhook_delivery_and_logging(): void
    {
        $this->actingAs($this->admin);

        // Create subscription
        $sub = WebhookSubscription::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->admin->id,
            'target_url' => 'https://example.com/webhook',
            'secret_token' => 'whsec_' . Str::random(24),
            'event_types' => ['booking.created'],
            'is_active' => true,
        ]);

        // Mock the transport to simulate a successful delivery
        $this->app->bind(WebhookTransport::class, function () {
            return new class implements WebhookTransport {
                public function send(string $url, string $payload, array $headers): array
                {
                    return [
                        'status' => 200,
                        'body' => '{"ok":true}',
                        'headers' => ['Content-Type' => ['application/json']],
                        'error' => null,
                    ];
                }
            };
        });

        // Dispatch synchronously
        DeliverWebhookJob::dispatchSync(
            $sub->id,
            'booking.created',
            ['id' => 'booking-123', 'status' => 'confirmed']
        );

        // Verify delivery log
        $log = WebhookDeliveryLog::where('webhook_subscription_id', $sub->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('delivered', $log->status);
        $this->assertEquals(200, $log->response_status);
        $this->assertEquals('booking.created', $log->event_type);

        // Verify CloudEvents envelope in payload
        $this->assertArrayHasKey('id', $log->payload);
        $this->assertArrayHasKey('status', $log->payload);

        // Verify signature headers were recorded
        $this->assertArrayHasKey('X-SODARS-Signature', $log->request_headers);
        $this->assertArrayHasKey('X-SODARS-Timestamp', $log->request_headers);
        $this->assertArrayHasKey('X-SODARS-Event', $log->request_headers);
        $this->assertArrayHasKey('X-SODARS-Delivery', $log->request_headers);
        $this->assertEquals('booking.created', $log->request_headers['X-SODARS-Event']);

        // Verify delivery logs endpoint
        $response = $this->getJson("/api/v1/integrations/webhooks/{$sub->id}/logs");
        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────
    // 6. Webhook Delivery Retry on Failure
    // ─────────────────────────────────────────────────────

    public function test_webhook_delivery_retry_on_failure(): void
    {
        $sub = WebhookSubscription::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->admin->id,
            'target_url' => 'https://example.com/failing-webhook',
            'secret_token' => 'whsec_' . Str::random(24),
            'event_types' => ['invoice.created'],
            'is_active' => true,
        ]);

        // Mock transport to simulate persistent failure
        $this->app->bind(WebhookTransport::class, function () {
            return new class implements WebhookTransport {
                public function send(string $url, string $payload, array $headers): array
                {
                    return [
                        'status' => 500,
                        'body' => 'Internal Server Error',
                        'headers' => [],
                        'error' => null,
                    ];
                }
            };
        });

        // With sync queue driver, the retry chain cascades:
        // attempt 1 → retrying → dispatches attempt 2 → retrying → dispatches attempt 3 → failed
        // So after dispatchSync(attempt=1), the final log state is 'failed' at attempt 3.
        DeliverWebhookJob::dispatchSync(
            $sub->id,
            'invoice.created',
            ['id' => 'inv-456'],
            1
        );

        // Final log should be 'failed' after exhausting all 3 retries
        $log = WebhookDeliveryLog::where('webhook_subscription_id', $sub->id)
            ->orderBy('updated_at', 'desc')
            ->first();
        $this->assertNotNull($log);
        $this->assertEquals('failed', $log->status);
        $this->assertEquals(3, $log->attempt);
        $this->assertEquals(500, $log->response_status);
    }

    // ─────────────────────────────────────────────────────
    // 7. Webhook Event Registry
    // ─────────────────────────────────────────────────────

    public function test_webhook_event_registry(): void
    {
        $events = WebhookEventRegistry::getEvents();

        $this->assertContains('booking.created', $events);
        $this->assertContains('booking.status_changed', $events);
        $this->assertContains('invoice.created', $events);
        $this->assertContains('inventory.created', $events);

        $this->assertTrue(WebhookEventRegistry::isValid('booking.created'));
        $this->assertFalse(WebhookEventRegistry::isValid('nonexistent.event'));
    }
}
