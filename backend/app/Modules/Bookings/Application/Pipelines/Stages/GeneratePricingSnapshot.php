<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Pipelines\Stages;

use App\Modules\Bookings\Domain\Entities\BookingItem;
use Closure;

class GeneratePricingSnapshot
{
    public function handle(array $passable, Closure $next): mixed
    {
        $prices = $passable['prices'];
        $items = $passable['items'];

        foreach ($items as $item) {
            $pricingDetails = $prices[$item->inventory_face_id];
            
            $snapshot = [
                'pricing_id' => $pricingDetails['pricing_id'],
                'pricing_type' => $pricingDetails['pricing_type'],
                'currency' => $passable['dto']->currency,
                'unit_rate' => $pricingDetails['net_price'],
                'markup' => $pricingDetails['retail_price'] - $pricingDetails['net_price'],
                'gst' => (int) round($pricingDetails['total_item_price'] * 0.18),
                'platform_fee' => (int) round($pricingDetails['total_item_price'] * 0.15),
                'provider_share' => $pricingDetails['total_item_price'] - ((int) round($pricingDetails['total_item_price'] * 0.15)),
                'commission' => (int) round($pricingDetails['total_item_price'] * 0.15),
            ];

            // Save JSON pricing snapshot in item table
            $item->update(['pricing_snapshot' => $snapshot]);
        }

        return $next($passable);
    }
}
