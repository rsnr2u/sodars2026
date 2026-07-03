<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Entities\InvoiceAdjustment;
use App\Modules\Finance\Domain\Entities\InvoiceActivity;
use App\Modules\Finance\Domain\Events\CreditNoteCreated;
use App\Modules\Finance\Domain\Events\DebitNoteCreated;
use App\Modules\Finance\Domain\Repositories\InvoiceReadRepositoryInterface;
use App\Modules\Finance\Domain\Repositories\InvoiceWriteRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class RecordAdjustmentAction
{
    public function __construct(
        protected InvoiceReadRepositoryInterface $readRepo,
        protected InvoiceWriteRepositoryInterface $writeRepo,
        protected OutboxService $outboxService
    ) {}

    public function execute(string $invoiceId, string $type, int $amountCents, string $reason): InvoiceAdjustment
    {
        return DB::transaction(function () use ($invoiceId, $type, $amountCents, $reason) {
            $invoice = $this->readRepo->findOrFail($invoiceId);

            $adjustment = InvoiceAdjustment::create([
                'id' => (string) Str::uuid(),
                'invoice_id' => $invoiceId,
                'adjustment_type' => $type,
                'amount_cents' => $amountCents,
                'reason' => $reason,
                'recorded_by' => auth()->id() ?? $invoice->customer_id,
            ]);

            // Adjust invoice grand total
            $currentTotal = $invoice->grand_total_cents;
            $newTotal = ($type === 'debit') ? ($currentTotal + $amountCents) : ($currentTotal - $amountCents);

            $this->writeRepo->update($invoiceId, [
                'grand_total_cents' => $newTotal,
            ]);

            $eventData = [
                'invoice_id' => $invoiceId,
                'adjustment_id' => $adjustment->id,
                'adjustment_type' => $type,
                'amount_cents' => $amountCents,
            ];

            if ($type === 'credit') {
                // Outbox
                $this->outboxService->record(
                    aggregateType: 'Invoice',
                    aggregateId: $invoiceId,
                    eventName: 'invoice.credit_note_created.v1',
                    data: $eventData,
                    eventVersion: 1,
                    schemaVersion: '1.0.0'
                );

                // Event
                Event::dispatch(new CreditNoteCreated(
                    aggregateId: $invoiceId,
                    aggregateVersion: 1,
                    data: $eventData,
                    occurredAt: now()->toIso8601String(),
                    correlationId: TraceContext::correlationId() ?? (string) Str::uuid(),
                    traceId: TraceContext::traceId() ?? (string) Str::uuid()
                ));
            } else {
                // Outbox
                $this->outboxService->record(
                    aggregateType: 'Invoice',
                    aggregateId: $invoiceId,
                    eventName: 'invoice.debit_note_created.v1',
                    data: $eventData,
                    eventVersion: 1,
                    schemaVersion: '1.0.0'
                );

                // Event
                Event::dispatch(new DebitNoteCreated(
                    aggregateId: $invoiceId,
                    aggregateVersion: 1,
                    data: $eventData,
                    occurredAt: now()->toIso8601String(),
                    correlationId: TraceContext::correlationId() ?? (string) Str::uuid(),
                    traceId: TraceContext::traceId() ?? (string) Str::uuid()
                ));
            }

            // Activity
            InvoiceActivity::create([
                'id' => (string) Str::uuid(),
                'invoice_id' => $invoiceId,
                'performed_by' => auth()->id() ?? $invoice->customer_id,
                'action' => 'AdjustmentRecorded',
                'description' => "Adjustment of type '{$type}' for {$amountCents} cents recorded. Reason: {$reason}.",
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
            ]);

            return $adjustment;
        });
    }
}
