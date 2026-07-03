<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Pipelines\Stages;

use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Entities\InvoiceItem;
use App\Modules\Finance\Domain\Entities\InvoiceTax;
use App\Modules\Finance\Domain\Enums\InvoiceStatus;
use App\Platform\Identifiers\InvoiceNumberGenerator;
use Closure;
use Illuminate\Support\Str;

class PersistInvoice
{
    public function __construct(protected InvoiceNumberGenerator $numberGenerator) {}

    public function handle(array $passable, Closure $next): mixed
    {
        $booking = $passable['booking'];
        $invoiceType = $passable['invoice_type'];

        $invoiceNumber = $this->numberGenerator->generate();

        $invoice = Invoice::create([
            'id' => (string) Str::uuid(),
            'invoice_number' => $invoiceNumber,
            'booking_id' => $booking->id,
            'customer_id' => $booking->customer_id,
            'branch_id' => $booking->branch_id,
            'issue_date' => $passable['issue_date'] ?? now()->toDateString(),
            'due_date' => $passable['due_date'] ?? now()->addDays(14)->toDateString(),
            'subtotal_cents' => $passable['subtotal_cents'],
            'discount_cents' => $passable['discount_cents'],
            'tax_cents' => $passable['tax_cents'],
            'grand_total_cents' => $passable['grand_total_cents'],
            'currency' => $booking->currency,
            'status' => InvoiceStatus::Draft->value,
            'invoice_type' => $invoiceType,
            'booking_snapshot' => $passable['booking_snapshot'],
        ]);

        foreach ($passable['items'] as $item) {
            InvoiceItem::create([
                'id' => (string) Str::uuid(),
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price_cents' => $item['unit_price_cents'],
                'total_price_cents' => $item['total_price_cents'],
                'pricing_snapshot' => $item['pricing_snapshot'],
            ]);
        }

        foreach ($passable['taxes_breakdown'] as $tax) {
            InvoiceTax::create([
                'id' => (string) Str::uuid(),
                'invoice_id' => $invoice->id,
                'tax_name' => $tax['tax_name'],
                'tax_rate_percentage' => $tax['tax_rate_percentage'],
                'tax_amount_cents' => $tax['tax_amount_cents'],
            ]);
        }

        $passable['invoice'] = $invoice;

        return $next($passable);
    }
}
