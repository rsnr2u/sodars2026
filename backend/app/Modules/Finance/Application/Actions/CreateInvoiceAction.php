<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Actions;

use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Finance\Application\DTOs\CreateInvoiceData;
use App\Modules\Finance\Application\Pipelines\GenerateInvoicePipeline;
use App\Modules\Finance\Domain\Entities\Invoice;

class CreateInvoiceAction
{
    public function __construct(protected GenerateInvoicePipeline $pipeline) {}

    public function execute(Booking $booking, string $invoiceType): Invoice
    {
        return $this->pipeline->execute([
            'booking' => $booking,
            'invoice_type' => $invoiceType,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
        ]);
    }
}
