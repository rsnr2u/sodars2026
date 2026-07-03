<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Infrastructure\Database\Seeders;

use App\Models\User;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Entities\BookingItem;
use App\Modules\Bookings\Domain\Entities\BookingStatusHistory;
use App\Modules\Bookings\Domain\Entities\BookingActivity;
use App\Modules\Bookings\Domain\Entities\Payment;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use App\Modules\Bookings\Domain\Enums\PaymentMethod;
use App\Modules\Bookings\Domain\Enums\PaymentStatus;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();
        $customer = User::role('customer_admin')->first() ?? User::first();
        $face = InventoryFace::first();

        if (!$branch || !$customer || !$face) {
            $this->command->warn('Skipping BookingSeeder: Dependencies missing.');
            return;
        }

        // 1. Create a draft booking
        $booking = Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => 'BK-2026-000001',
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(15)->toDateString(),
            'subtotal_cents' => 3000000,
            'discount_cents' => 0,
            'tax_cents' => 540000,
            'platform_fee_cents' => 450000,
            'provider_share_cents' => 2550000,
            'commission_cents' => 450000,
            'grand_total_cents' => 3540000,
            'currency' => 'INR',
            'status' => BookingStatus::Draft->value,
        ]);

        $item = BookingItem::create([
            'id' => (string) Str::uuid(),
            'booking_id' => $booking->id,
            'inventory_face_id' => $face->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(15)->toDateString(),
            'daily_frequency' => 1,
            'net_price_cents' => 2727272,
            'markup_percentage' => 10,
            'retail_price_cents' => 3000000,
            'total_item_price_cents' => 3000000,
            'pricing_snapshot' => [
                'pricing_id' => (string) Str::uuid(),
                'pricing_type' => 'baseline',
                'currency' => 'INR',
                'unit_rate' => 2727272,
                'markup' => 272728,
                'gst' => 540000,
                'platform_fee' => 450000,
                'provider_share' => 2550000,
                'commission' => 450000,
            ],
        ]);

        BookingStatusHistory::create([
            'id' => (string) Str::uuid(),
            'booking_id' => $booking->id,
            'changed_by' => $customer->id,
            'from_status' => BookingStatus::Draft->value,
            'to_status' => BookingStatus::Draft->value,
            'comment' => 'Initial checkout draft state.',
        ]);

        BookingActivity::create([
            'id' => (string) Str::uuid(),
            'booking_id' => $booking->id,
            'performed_by' => $customer->id,
            'event_name' => 'booking.created.v1',
            'action' => 'Created',
            'old_values' => null,
            'new_values' => $booking->toArray(),
            'ip' => '127.0.0.1',
            'user_agent' => 'Seeder',
            'trace_id' => (string) Str::uuid(),
        ]);
    }
}
