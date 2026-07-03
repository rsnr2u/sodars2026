<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use App\Modules\Bookings\Domain\Events\BookingCreated;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Platform\Automation\Domain\Entities\AutomationRule;
use App\Platform\Automation\Domain\Entities\AutomationExecution;
use App\Platform\Notifications\Database\Seeders\NotificationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class AutomationApiTest extends ApiTestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(NotificationSeeder::class);

        // Create Branch
        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'HQ Branch',
            'code' => 'HQ-B',
            'support_email' => 'hq@sodars.com',
            'support_phone' => '+91800100',
        ]);

        $this->user = User::factory()->create([
            'email' => 'customer.test@sodars.com',
        ]);
    }

    public function test_automation_rules_evaluation_and_actions(): void
    {
        // 1. Create a Booking under 5,000 INR (e.g. 4,200 INR = 420000 cents)
        $booking = Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => 'BK-AUTO-MATCH',
            'customer_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'subtotal_cents' => 400000,
            'tax_cents' => 20000,
            'grand_total_cents' => 420000,
            'currency' => 'INR',
            'status' => BookingStatus::Draft,
        ]);

        // 2. Configure Automation Rule
        $rule = AutomationRule::create([
            'id' => (string) Str::uuid(),
            'name' => 'Auto Approve Cheap Bookings',
            'key' => 'booking.auto_approval',
            'version' => 1,
            'event_class' => BookingCreated::class,
            'conditions' => [
                'logical_operator' => 'and',
                'rules' => [
                    [
                        'field' => 'booking.grand_total_cents',
                        'operator' => '<',
                        'value' => 500000,
                    ],
                    [
                        'field' => 'booking.currency',
                        'operator' => '==',
                        'value' => 'INR',
                    ]
                ]
            ],
            'actions' => [
                [
                    'type' => 'status.update',
                    'params' => [
                        'status' => 'approved',
                    ]
                ],
                [
                    'type' => 'notification.send',
                    'params' => [
                        'template_key' => 'booking.created',
                    ]
                ]
            ],
            'is_active' => true,
        ]);

        // 3. Dispatch Event
        $event = new BookingCreated(
            $booking->id,
            1,
            $booking->toArray(),
            now()->toIso8601String(),
            (string) Str::uuid(),
            (string) Str::uuid(),
            $this->user->id
        );

        event($event);

        // 4. Assertions
        // Verify automation rule was executed successfully
        $this->assertDatabaseHas('automation_executions', [
            'rule_id' => $rule->id,
            'status' => 'success',
        ]);

        // Verify booking status was auto updated to Approved!
        $booking->refresh();
        $this->assertEquals(BookingStatus::Approved, $booking->status);

        // Verify notification dispatch record was created
        $this->assertDatabaseHas('notification_dispatches', [
            'recipient_id' => $this->user->id,
        ]);
    }

    public function test_automation_rules_evaluation_no_match(): void
    {
        // Create Booking over limit (e.g. 6,000 INR = 600000 cents)
        $booking = Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => 'BK-AUTO-NOMATCH',
            'customer_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'subtotal_cents' => 550000,
            'tax_cents' => 50000,
            'grand_total_cents' => 600000,
            'currency' => 'INR',
            'status' => BookingStatus::Draft,
        ]);

        AutomationRule::create([
            'id' => (string) Str::uuid(),
            'name' => 'Auto Approve Cheap Bookings',
            'key' => 'booking.auto_approval',
            'version' => 1,
            'event_class' => BookingCreated::class,
            'conditions' => [
                'logical_operator' => 'and',
                'rules' => [
                    [
                        'field' => 'booking.grand_total_cents',
                        'operator' => '<',
                        'value' => 500000,
                    ],
                ]
            ],
            'actions' => [
                [
                    'type' => 'status.update',
                    'params' => [
                        'status' => 'approved',
                    ]
                ]
            ],
            'is_active' => true,
        ]);

        $event = new BookingCreated(
            $booking->id,
            1,
            $booking->toArray(),
            now()->toIso8601String(),
            (string) Str::uuid(),
            (string) Str::uuid(),
            $this->user->id
        );

        event($event);

        // Should be skipped (conditions not matching grand total cents of 600000)
        $this->assertDatabaseHas('automation_executions', [
            'status' => 'skipped',
        ]);

        // Booking status remains Draft
        $booking->refresh();
        $this->assertEquals(BookingStatus::Draft, $booking->status);
    }
}
