<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\DTOs;

use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Finance\Domain\Enums\InvoiceType;
use Illuminate\Http\Request;

class CreateInvoiceData
{
    public function __construct(
        public readonly string $bookingId,
        public readonly string $invoiceType, // proforma_invoice, tax_invoice
        public readonly ?string $issueDate = null,
        public readonly ?string $dueDate = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            bookingId: $request->input('booking_id'),
            invoiceType: $request->input('invoice_type', InvoiceType::ProformaInvoice->value),
            issueDate: $request->input('issue_date'),
            dueDate: $request->input('due_date')
        );
    }
}
