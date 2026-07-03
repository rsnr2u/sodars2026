<?php

declare(strict_types=1);

namespace App\Platform\Money;

use App\Core\ValueObjects\Money;
use App\Platform\Money\Contracts\DiscountStrategy;

class DiscountCalculator implements DiscountStrategy
{
    /**
     * Compute relative discount amount.
     */
    public function calculatePercentageDiscount(Money $amount, float $percentage, RoundingPolicy $roundingPolicy = RoundingPolicy::HalfUp): Money
    {
        $discountCents = $amount->getAmount() * ($percentage / 100);
        $rounded = $roundingPolicy->round($discountCents);

        return new Money($rounded, $amount->getCurrency());
    }
}
