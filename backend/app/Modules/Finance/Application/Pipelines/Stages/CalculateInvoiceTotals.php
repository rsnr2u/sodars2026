<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Pipelines\Stages;

use App\Modules\Finance\Domain\Services\Invoicing\InvoiceCalculator;
use App\Modules\Finance\Domain\Services\Invoicing\GSTCalculator;
use Closure;

class CalculateInvoiceTotals
{
    public function __construct(
        protected InvoiceCalculator $invoiceCalculator,
        protected GSTCalculator $gstCalculator
    ) {}

    public function handle(array $passable, Closure $next): mixed
    {
        $booking = $passable['booking'];

        // 1. Calculate base subtotals and details
        $calculations = $this->invoiceCalculator->calculate($booking);

        // 2. Resolve GST tax breakdown
        $branch = $booking->branch;
        $customer = $booking->customer;
        
        $taxes = $this->gstCalculator->calculateTaxes(
            $calculations['subtotal_cents'] - $calculations['discount_cents'],
            $branch,
            $customer
        );

        $passable['subtotal_cents'] = $calculations['subtotal_cents'];
        $passable['discount_cents'] = $calculations['discount_cents'];
        $passable['tax_cents'] = $taxes['total_tax_cents'];
        $passable['grand_total_cents'] = ($calculations['subtotal_cents'] - $calculations['discount_cents']) + $taxes['total_tax_cents'];
        $passable['items'] = $calculations['items'];
        $passable['taxes_breakdown'] = $taxes['breakdown'];

        return $next($passable);
    }
}
