<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Services\Invoicing;

use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Finance\Domain\Entities\Invoice;

class InvoiceCalculator
{
    /**
     * Compute subtotals, discounts, and item pricing splits.
     */
    public function calculate(Booking $booking): array
    {
        $subtotal = 0;
        $items = [];

        foreach ($booking->items as $item) {
            $pricing = $item->pricing_snapshot;
            $net = $pricing['unit_rate'] ?? $item->net_price_cents;
            $markup = $pricing['markup'] ?? 0;
            $retailPrice = $net + $markup;
            $totalPrice = $retailPrice * ($item->daily_frequency ?? 1);

            $subtotal += $totalPrice;

            $items[] = [
                'description' => "Ad Display on Face #{$item->inventory_face_id} from {$item->start_date->toDateString()} to {$item->end_date->toDateString()}",
                'quantity' => $item->daily_frequency ?? 1,
                'unit_price_cents' => $retailPrice,
                'total_price_cents' => $totalPrice,
                'pricing_snapshot' => $pricing,
            ];
        }

        return [
            'subtotal_cents' => $subtotal,
            'discount_cents' => $booking->discount_cents ?? 0,
            'items' => $items,
        ];
    }
}
