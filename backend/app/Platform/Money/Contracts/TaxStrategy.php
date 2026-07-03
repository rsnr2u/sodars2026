<?php

declare(strict_types=1);

namespace App\Platform\Money\Contracts;

use App\Core\ValueObjects\Money;
use App\Platform\Money\RoundingPolicy;

interface TaxStrategy
{
    public function calculateTax(Money $amount, float $ratePercentage, RoundingPolicy $roundingPolicy): Money;

    public function calculateNetFromGross(Money $gross, float $ratePercentage, RoundingPolicy $roundingPolicy): Money;
}
