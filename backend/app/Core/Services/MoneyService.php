<?php

declare(strict_types=1);

namespace App\Core\Services;

use App\Core\ValueObjects\Currency;
use App\Core\ValueObjects\Money;

class MoneyService
{
    protected string $defaultCurrencyCode;

    public function __construct()
    {
        $this->defaultCurrencyCode = config('app.currency', 'INR');
    }

    /**
     * Helper to create a Money object in default currency.
     */
    public function create(int $amountCents): Money
    {
        return new Money($amountCents, new Currency($this->defaultCurrencyCode));
    }

    /**
     * Calculate tax (GST) amount for a given base amount and rate percentage.
     */
    public function calculateTax(Money $amount, float $taxRatePercent): Money
    {
        return $amount->multiply($taxRatePercent / 100);
    }

    /**
     * Apply marketplace markup amount.
     */
    public function applyMarkup(Money $netPrice, float $markupPercent): Money
    {
        $markupAmount = $netPrice->multiply($markupPercent / 100);

        return $netPrice->add($markupAmount);
    }

    /**
     * Compute commission share.
     */
    public function calculateCommission(Money $retailPrice, float $commissionPercent): Money
    {
        return $retailPrice->multiply($commissionPercent / 100);
    }
}
