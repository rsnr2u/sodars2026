<?php

declare(strict_types=1);

namespace App\Platform\Money\Contracts;

use App\Core\ValueObjects\Money;
use App\Platform\Money\RoundingPolicy;

interface DiscountStrategy
{
    public function calculatePercentageDiscount(Money $amount, float $percentage, RoundingPolicy $roundingPolicy): Money;
}
