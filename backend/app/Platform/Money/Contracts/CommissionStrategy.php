<?php

declare(strict_types=1);

namespace App\Platform\Money\Contracts;

use App\Core\ValueObjects\Money;
use App\Platform\Money\RoundingPolicy;

interface CommissionStrategy
{
    public function calculateFee(Money $amount, float $ratePercentage, RoundingPolicy $roundingPolicy): Money;
}
