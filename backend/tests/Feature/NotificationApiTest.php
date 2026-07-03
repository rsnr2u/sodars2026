<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Events\BookingCreated;
use App\Modules\Bookings\Domain\Events\BookingStatusChanged;
use App\Platform\Notifications\Application\Jobs\SendNotificationJob;
use App\Platform\Notifications\Domain\Entities\NotificationChannel;
use App\Platform\Notifications\Domain\Entities\NotificationDispatch;
use App\Platform\Notifications\Domain\Entities\NotificationTemplate;
use App\Platform\Notifications\Domain\Entities\NotificationPreference;
use App\Platform\Notifications\Domain\Entities\InAppNotification;
use App\Platform\Notifications\Database\Seeders\NotificationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class NotificationApiTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed default roles and notifications templates
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(NotificationSeeder::class);
    }

    public function test_notification_seeder_and_active_templates(): void
    {
        $this->assertDatabaseHas('notification_channels', ['key' => 'email']);
        $this->assertDatabaseHas('notification_channels', ['key' => 'in_app']);

        $template = NotificationTemplate::where('key', 'booking.created')->first();
        $this->assertNotNull($template);
        $this->assertEquals('transactional', $template->category->value);

        $version = $template->versions()->where('is_active', true)->first();
        $this->assertNotNull($version);
        $this->assertStringContainsString('{{booking.booking_code}}', $version->subject);
    }

    public function test_nested_placeholder_compilation(): void
    {
        $compiler = app(\App\Platform\Notifications\Application\TemplateCompiler\TemplateCompiler::class);

        $templateText = "Hello {{customer.name}}, your booking {{booking.booking_code}} is {{booking.status}}.";
        $context = [
            'customer' => ['name' => 'John Doe'],
            'booking' => [
                'booking_code' => 'BK-1002',
                'status' => 'confirmed'
            ]
        ];

        $compiled = $compiler->compile($templateText, $context);
        $this->assertEquals("Hello John Doe, your booking BK-1002 is confirmed.", $compiled);
    }

    public function test_event_driven_notification_dispatch_and_preferences(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
        ]);

        // Create branch to satisfy foreign key constraints
        $branch = \App\Modules\Branches\Domain\Entities\Branch::create([
            'name' => 'HQ Branch',
            'code' => 'HQ-B',
            'support_email' => 'hq@sodars.com',
            'support_phone' => '+91800100',
        ]);

        // Create a mock booking
        $booking = Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => 'BK-2026-X01',
            'customer_id' => $user->id,
            'branch_id' => $branch->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'subtotal_cents' => 1500000,
            'tax_cents' => 270000,
            'grand_total_cents' => 1770000,
            'currency' => 'INR',
            'status' => \App\Modules\Bookings\Domain\Enums\BookingStatus::Draft,
        ]);

        // Attach custom customer email/details mock by mocking relationship or matching email in first user
        // BookingCreated parameters: aggregateId, aggregateVersion, data, occurredAt, correlationId, traceId
        $event = new BookingCreated(
            $booking->id,
            1,
            [],
            now()->toIso8601String(),
            (string) Str::uuid(),
            (string) Str::uuid(),
            $user->id
        );

        // Fire event
        event($event);

        // Assert SendNotificationJob was pushed to queue for configured channels (email, in_app)
        Queue::assertPushed(SendNotificationJob::class);

        // Verify dispatches exist
        $dispatches = NotificationDispatch::where('recipient_id', $user->id)->get();
        $this->assertTrue($dispatches->count() > 0);

        // Ensure we logged correct channel keys
        $channels = $dispatches->pluck('channel')->toArray();
        $this->assertContains('email', $channels);
        $this->assertContains('in_app', $channels);

        // Now test preferences constraints: Disable Email notifications for transactional category
        NotificationPreference::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'category' => 'transactional',
            'channel' => 'email',
            'is_enabled' => false,
        ]);

        // Clear dispatches to isolate second run
        NotificationDispatch::query()->delete();

        // Fire another event (BookingCreated)
        event($event);

        // Only in_app should be pushed, email should be skipped for booking.created template
        $newDispatches = NotificationDispatch::where('recipient_id', $user->id)
            ->whereHas('template', function ($q) {
                $q->where('key', 'booking.created');
            })
            ->where('status', \App\Platform\Notifications\Domain\Enums\NotificationStatus::QUEUED)
            ->get();

        $newChannels = $newDispatches->pluck('channel')->toArray();
        $this->assertNotContains('email', $newChannels);
    }

    public function test_in_app_notifications_feed_and_preference_update_endpoints(): void
    {
        $user = $this->actingAsAdmin();

        // 1. Create a dummy in-app alert
        $alert = InAppNotification::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Invoice Raised',
            'message' => 'Your invoice for INR 45,000 is ready.',
            'type' => 'success',
            'is_read' => false,
        ]);

        // 2. Fetch Notifications Feed via API
        $response = $this->getJson(route('notifications.index'));
        $this->assertApiResponse($response, 200);

        $response->assertJsonFragment([
            'title' => 'Invoice Raised',
            'message' => 'Your invoice for INR 45,000 is ready.',
        ]);

        // 3. Mark notification as read
        $readResponse = $this->postJson(route('notifications.read', ['id' => $alert->id]));
        $this->assertApiResponse($readResponse, 200);
        $this->assertTrue($alert->fresh()->is_read);

        // 4. Update Preferences endpoint
        $prefResponse = $this->postJson(route('notifications.preferences'), [
            'category' => 'marketing',
            'channel' => 'sms',
            'is_enabled' => false,
        ]);
        $this->assertApiResponse($prefResponse, 200);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'category' => 'marketing',
            'channel' => 'sms',
            'is_enabled' => false,
        ]);
    }
}
