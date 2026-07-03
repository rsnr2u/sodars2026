<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Entities\InvoiceActivity;
use App\Modules\Finance\Domain\Enums\InvoiceStatus;
use App\Modules\Finance\Domain\Events\InvoicePaymentRecorded;
use App\Modules\Finance\Domain\Repositories\InvoiceReadRepositoryInterface;
use App\Modules\Finance\Domain\Repositories\InvoiceWriteRepositoryInterface;
use App\Modules\Bookings\Domain\Entities\Payment;
use App\Modules\Bookings\Domain\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class RecordInvoicePaymentAction
{
    public function __construct(
        protected InvoiceReadRepositoryInterface $readRepo,
        protected InvoiceWriteRepositoryInterface $writeRepo,
        protected OutboxService $outboxService
    ) {}

    public function execute(string $invoiceId, int $amountCents, string $method, string $reference): Invoice
    {
        return DB::transaction(function () use ($invoiceId, $amountCents, $method, $reference) {
            $invoice = $this->readRepo->findOrFail($invoiceId);

            // Record a polymorphic payment reference linked to this invoice
            $payment = Payment::create([
                'id' => (string) Str::uuid(),
                'paymentable_id' => $invoiceId,
                'paymentable_type' => Invoice::class,
                'payment_method' => $method,
                'amount_cents' => $amountCents,
                'reference_number' => $reference,
                'status' => PaymentStatus::Verified->value,
                'recorded_by' => auth()->id() ?? $invoice->customer_id,
            ]);

            // Sum all payments verified for this invoice
            $totalPaid = Payment::where('paymentable_id', $invoiceId)
                ->where('paymentable_type', Invoice::class)
                ->where('status', PaymentStatus::Verified->value)
                ->sum('amount_cents');

            $status = InvoiceStatus::PartiallyPaid->value;
            if ($totalPaid >= $invoice->grand_total_cents) {
                $status = InvoiceStatus::Paid->value;
            }

            $invoice = $this->writeRepo->update($invoiceId, [
                'status' => $status,
            ]);

            $eventData = [
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'amount_cents' => $amountCents,
                'status' => $invoice->status->value,
            ];

            // Outbox
            $this->outboxService->record(
                aggregateType: 'Invoice',
                aggregateId: $invoice->id,
                eventName: 'invoice.payment.recorded.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // Event
            Event::dispatch(new InvoicePaymentRecorded(
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
                'action' => 'PaymentRecorded',
                'description' => "Payment of {$amountCents} cents recorded for invoice. Reference: {$reference}.",
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
            ]);

            return $invoice;
        });
    }
}
