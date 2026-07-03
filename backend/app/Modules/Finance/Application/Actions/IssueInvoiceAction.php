<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Entities\InvoiceActivity;
use App\Modules\Finance\Domain\Enums\InvoiceStatus;
use App\Modules\Finance\Domain\Events\InvoiceIssued;
use App\Modules\Finance\Domain\Repositories\InvoiceWriteRepositoryInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class IssueInvoiceAction
{
    public function __construct(
        protected InvoiceWriteRepositoryInterface $writeRepo,
        protected OutboxService $outboxService
    ) {}

    public function execute(string $invoiceId): Invoice
    {
        $invoice = $this->writeRepo->update($invoiceId, [
            'status' => InvoiceStatus::Issued->value,
            'issue_date' => now()->toDateString(),
        ]);

        $eventData = [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'grand_total_cents' => $invoice->grand_total_cents,
        ];

        // Outbox
        $this->outboxService->record(
            aggregateType: 'Invoice',
            aggregateId: $invoice->id,
            eventName: 'invoice.issued.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // Event
        Event::dispatch(new InvoiceIssued(
            aggregateId: $invoice->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId() ?? (string) Str::uuid(),
            traceId: TraceContext::traceId() ?? (string) Str::uuid()
        ));

        // Activity
        InvoiceActivity::create([
            'id' => (string) Str::uuid(),
            'invoice_id' => $invoice->id,
            'performed_by' => auth()->id() ?? $invoice->customer_id,
            'action' => 'Issued',
            'description' => "Invoice #{$invoice->invoice_number} transitioned from draft to issued.",
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
        ]);

        return $invoice;
    }
}
