<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Pipelines\Stages;

use App\Core\ValueObjects\Currency;
use App\Core\ValueObjects\DateRange;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Inventory\Domain\Services\InventoryPricingResolver;
use Closure;

class ResolvePricing
{
    public function __construct(
        protected InventoryPricingResolver $resolver
    ) {}

    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];
        $currencyObj = new Currency($dto->currency);

        $resolvedPrices = [];

        foreach ($dto->items as $item) {
            $face = InventoryFace::with('pricings')->find($item->inventoryFaceId);
            $period = new DateRange(
                new \DateTimeImmutable($item->startDate),
                new \DateTimeImmutable($item->endDate)
            );

            // Compute subtotal rate copy
            $money = $this->resolver->resolve($face, $period, $currencyObj);
            
            // Get raw markup and platform splits (fetch active pricing copy matching the currency)
            $pricing = $face->pricings()
                ->where('currency', $dto->currency)
                ->first();
            $markupPct = 10; // Default branch markup to 10% for Version 1
            
            // Platform splits
            $netPrice = $money->getAmount();
            $markupAmount = (int) round($netPrice * ($markupPct / 100));
            $retailPrice = $netPrice + $markupAmount;

            $resolvedPrices[$item->inventoryFaceId] = [
                'net_price' => $netPrice,
                'markup_percentage' => $markupPct,
                'retail_price' => $retailPrice,
                'total_item_price' => $retailPrice * $item->dailyFrequency,
                'pricing_id' => $pricing?->id,
                'pricing_type' => $pricing ? ($pricing->pricing_type instanceof \UnitEnum ? $pricing->pricing_type->value : (string) $pricing->pricing_type) : 'baseline',
            ];
        }

        $passable['prices'] = $resolvedPrices;

        return $next($passable);
    }
}
