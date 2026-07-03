<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Services\Settlement;

use App\Modules\Bookings\Domain\Entities\Booking;

class SettlementCalculator
{
    /**
     * Calculate settlement totals splits from a Booking and Invoice.
     * Splitting:
     * - Retail total (collected client amount)
     * - Commission (15% default platform share)
     * - Provider share (Retail - Commission)
     */
    public function calculate(Booking $booking): array
    {
        $totalRetail = 0;
        $totalCommission = 0;
        $totalProviderShare = 0;
        $items = [];

        foreach ($booking->items as $item) {
            $pricing = $item->pricing_snapshot;
            $net = $pricing['unit_rate'] ?? $item->net_price_cents;
            $markup = $pricing['markup'] ?? 0;
            $retail = $net + $markup;
            $totalItemPrice = $retail * ($item->daily_frequency ?? 1);

            $platformFee = (int) round($totalItemPrice * 0.15); // 15% platform commission
            $providerShare = $totalItemPrice - $platformFee;

            $totalRetail += $totalItemPrice;
            $totalCommission += $platformFee;
            $totalProviderShare += $providerShare;

            $items[] = [
                'booking_item_id' => $item->id,
                'amount_cents' => $providerShare,
            ];
        }

        return [
            'total_amount_cents' => $totalRetail,
            'provider_share_cents' => $totalProviderShare,
            'commission_cents' => $totalCommission,
            'tax_cents' => (int) round($totalRetail * 0.18), // 18% GST collected for billing
            'items' => $items,
        ];
    }
}
