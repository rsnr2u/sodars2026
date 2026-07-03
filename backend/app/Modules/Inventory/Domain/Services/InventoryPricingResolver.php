<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Services;

use App\Core\ValueObjects\Currency;
use App\Core\ValueObjects\DateRange;
use App\Core\ValueObjects\Money;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use InvalidArgumentException;

class InventoryPricingResolver
{
    /**
     * Resolve the price of a face for a given period and currency.
     */
    public function resolve(InventoryFace $face, DateRange $period, Currency $currency): Money
    {
        $startDate = Carbon::instance($period->getStartDate())->startOfDay();
        $endDate = Carbon::instance($period->getEndDate())->startOfDay();
        $carbonPeriod = CarbonPeriod::create($startDate, $endDate);

        $totalCents = 0;
        $currencyCode = $currency->getCode();

        // Load pricings
        $pricings = $face->pricings()
            ->where('currency', $currencyCode)
            ->get();

        foreach ($carbonPeriod as $date) {
            $day = Carbon::instance($date)->startOfDay();
            
            // Filter pricings that are active on this day
            $activePricing = $pricings->filter(function ($pricing) use ($day) {
                $from = Carbon::parse($pricing->effective_from)->startOfDay();
                $to = $pricing->effective_to ? Carbon::parse($pricing->effective_to)->startOfDay() : null;

                return $from->lte($day) && ($to === null || $to->gte($day));
            })
            ->sortByDesc('priority')
            ->first();

            if (!$activePricing) {
                throw new InvalidArgumentException(
                    sprintf("Could not resolve pricing for face %s on date %s in currency %s", $face->face_code, $day->toDateString(), $currencyCode)
                );
            }

            $totalCents += $activePricing->rate_cents;
        }

        return new Money($totalCents, $currency);
    }
}
