<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Pipelines\Stages;

use App\Core\ValueObjects\Money;
use App\Core\ValueObjects\Currency;
use App\Platform\Money\TaxCalculator;
use App\Platform\Money\CommissionCalculator;
use App\Platform\Money\FinancialSummary;
use Closure;

class CalculateFinancialSummary
{
    public function __construct(
        protected TaxCalculator $taxCalculator,
        protected CommissionCalculator $commissionCalculator
    ) {}

    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];
        $prices = $passable['prices'];
        $currencyObj = new Currency($dto->currency);

        $subtotal = 0;
        foreach ($prices as $price) {
            $subtotal += $price['total_item_price'];
        }

        $subtotalMoney = new Money($subtotal, $currencyObj);
        
        // 18% default GST/VAT rate
        $taxMoney = $this->taxCalculator->calculateTax($subtotalMoney, 18.0);
        
        // 15% platform commission fee rate from total retail amount
        $platformFeeMoney = $this->commissionCalculator->calculateFee($subtotalMoney, 15.0);

        // Provider gets retail subtotal minus platform commission fee
        $providerShareCents = $subtotal - $platformFeeMoney->getAmount();
        $providerShareMoney = new Money($providerShareCents, $currencyObj);

        $grandTotalCents = $subtotal + $taxMoney->getAmount();
        $grandTotalMoney = new Money($grandTotalCents, $currencyObj);

        $summary = new FinancialSummary(
            subtotal: $subtotalMoney,
            discount: new Money(0, $currencyObj),
            tax: $taxMoney,
            platformFee: $platformFeeMoney,
            providerShare: $providerShareMoney,
            commission: $platformFeeMoney,
            grandTotal: $grandTotalMoney
        );

        $passable['financial_summary'] = $summary;

        return $next($passable);
    }
}
