<?php

declare(strict_types=1);

namespace App\Platform\Money;

use App\Core\ValueObjects\Money;
use App\Platform\Money\Contracts\TaxStrategy;

class TaxCalculator implements TaxStrategy
{
    /**
     * Calculate tax amount (e.g. GST) for a net value.
     */
    public function calculateTax(Money $amount, float $ratePercentage, RoundingPolicy $roundingPolicy = RoundingPolicy::HalfUp): Money
    {
        $taxCents = $amount->getAmount() * ($ratePercentage / 100);
        $rounded = $roundingPolicy->round($taxCents);

        return new Money($rounded, $amount->getCurrency());
    }

    /**
     * Derive net value from a tax-inclusive gross value.
     */
    public function calculateNetFromGross(Money $gross, float $ratePercentage, RoundingPolicy $roundingPolicy = RoundingPolicy::HalfUp): Money
    {
        $factor = 1 + ($ratePercentage / 100);
        $netCents = $gross->getAmount() / $factor;
        $rounded = $roundingPolicy->round($netCents);

        return new Money($rounded, $gross->getCurrency());
    }
}
