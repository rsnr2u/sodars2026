<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Pipelines\Stages;

use App\Modules\Inventory\Domain\Entities\InventoryPricing;
use Closure;

class CreatePricingStage
{
    /**
     * Create baseline pricing rates.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];
        $inventory = $passable['inventory'];

        $pricingData = $dto->pricing;
        if (empty($pricingData)) {
            $pricingData = [[
                'pricing_type' => 'baseline',
                'rate_cents' => 50000,
                'currency' => 'INR',
                'tax_inclusive' => false,
                'minimum_booking_days' => 1,
                'effective_from' => now()->toDateTimeString(),
                'effective_to' => null,
                'priority' => 0,
            ]];
        }

        // Apply pricing to all faces of this inventory
        $faces = $inventory->faces;
        foreach ($faces as $face) {
            foreach ($pricingData as $pricing) {
                InventoryPricing::create([
                    'inventory_face_id' => $face->id,
                    'pricing_type' => $pricing['pricing_type'] ?? 'baseline',
                    'rate_cents' => (int) $pricing['rate_cents'],
                    'currency' => $pricing['currency'] ?? 'INR',
                    'tax_inclusive' => (bool) ($pricing['tax_inclusive'] ?? false),
                    'minimum_booking_days' => (int) ($pricing['minimum_booking_days'] ?? 1),
                    'effective_from' => $pricing['effective_from'] ?? now(),
                    'effective_to' => $pricing['effective_to'] ?? null,
                    'priority' => (int) ($pricing['priority'] ?? 0),
                ]);
            }
        }

        return $next($passable);
    }
}
