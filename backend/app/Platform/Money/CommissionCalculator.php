<?php

declare(strict_types=1);

namespace App\Platform\Money;

use App\Core\ValueObjects\Money;
use App\Platform\Money\Contracts\CommissionStrategy;

class CommissionCalculator implements CommissionStrategy
{
    /**
     * Calculate fee cuts or partner shares.
     */
    public function calculateFee(Money $amount, float $ratePercentage, RoundingPolicy $roundingPolicy = RoundingPolicy::HalfUp): Money
    {
        $feeCents = $amount->getAmount() * ($ratePercentage / 100);
        $rounded = $roundingPolicy->round($feeCents);

        return new Money($rounded, $amount->getCurrency());
    }
}
