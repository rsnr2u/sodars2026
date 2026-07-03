<?php

declare(strict_types=1);

namespace App\Platform\Money;

use App\Core\ValueObjects\Money;
use App\Core\ValueObjects\Currency;
use InvalidArgumentException;

class ExchangeRateService
{
    /**
     * Stub convert for v1 (assumes 1:1 or defaults if same currency, otherwise throws error).
     */
    public function convert(Money $amount, Currency $targetCurrency): Money
    {
        if ($amount->getCurrency()->equals($targetCurrency)) {
            return $amount;
        }

        throw new InvalidArgumentException(
            sprintf(
                "Multi-currency conversion from %s to %s is not configured for Version 1.",
                $amount->getCurrency()->getCode(),
                $targetCurrency->getCode()
            )
        );
    }
}
