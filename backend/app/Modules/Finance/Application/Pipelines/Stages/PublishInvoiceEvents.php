<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Pipelines\Stages;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Finance\Domain\Events\InvoiceCreated;
use App\Modules\Finance\Domain\Entities\InvoiceActivity;
use Closure;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class PublishInvoiceEvents
{
    public function __construct(protected OutboxService $outboxService) {}

    public function handle(array $passable, Closure $next): mixed
    {
        $invoice = $passable['invoice'];

        $eventData = [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'booking_id' => $invoice->booking_id,
            'grand_total_cents' => $invoice->grand_total_cents,
            'invoice_type' => $invoice->invoice_type->value ?? $invoice->invoice_type,
            'status' => $invoice->status->value ?? $invoice->status,
        ];

        // 1. Transactional Outbox
        $this->outboxService->record(
            aggregateType: 'Invoice',
            aggregateId: $invoice->id,
            eventName: 'finance.invoice.created.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 2. Dispatch Local Domain Event
        Event::dispatch(new InvoiceCreated(
            aggregateId: $invoice->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId() ?? (string) Str::uuid(),
            traceId: TraceContext::traceId() ?? (string) Str::uuid()
        ));

        // 3. Activity Timeline
        InvoiceActivity::create([
            'id' => (string) Str::uuid(),
            'invoice_id' => $invoice->id,
            'performed_by' => auth()->id() ?? $invoice->customer_id,
            'action' => 'Created',
            'description' => "Invoice #{$invoice->invoice_number} generated from booking.",
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
        ]);

        return $next($passable);
    }
}
