<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Services;

use App\Core\Services\OutboxService;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Events\InvoiceCreated;
use App\Modules\Finance\Domain\Events\InvoiceIssued;
use App\Modules\Finance\Domain\Events\InvoicePaid;
use App\Modules\Finance\Domain\Events\InvoiceVoided;
use Illuminate\Support\Facades\Event;

class InvoiceLifecycleService
{
    public function __construct(
        protected OutboxService $outboxService
    ) {}

    /**
     * Record invoice creation in outbox and fire domain event.
     */
    public function recordCreation(Invoice $invoice): void
    {
        $eventData = [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'grand_total_cents' => $invoice->grand_total_cents,
        ];

        $metadata = [
            'customer_id' => $invoice->customer_id,
            'branch_id' => $invoice->branch_id,
            'grand_total' => $invoice->grand_total_cents,
            'currency' => $invoice->currency,
        ];

        $this->outboxService->record(
            aggregateType: 'Invoice',
            aggregateId: $invoice->id,
            eventName: 'finance.invoice.created.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new InvoiceCreated(
            entityClass: Invoice::class,
            aggregateId: $invoice->id,
            aggregateVersion: 1,
            data: $eventData,
            metadata: $metadata
        ));
    }

    /**
     * Record invoice issue event.
     */
    public function recordIssue(Invoice $invoice): void
    {
        $eventData = [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'grand_total_cents' => $invoice->grand_total_cents,
        ];

        $metadata = [
            'customer_id' => $invoice->customer_id,
            'branch_id' => $invoice->branch_id,
            'grand_total' => $invoice->grand_total_cents,
            'currency' => $invoice->currency,
        ];

        $this->outboxService->record(
            aggregateType: 'Invoice',
            aggregateId: $invoice->id,
            eventName: 'finance.invoice.issued.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new InvoiceIssued(
            entityClass: Invoice::class,
            aggregateId: $invoice->id,
            aggregateVersion: 1,
            data: $eventData,
            metadata: $metadata
        ));
    }

    /**
     * Record invoice paid event.
     */
    public function recordPaid(Invoice $invoice): void
    {
        $eventData = [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'grand_total_cents' => $invoice->grand_total_cents,
        ];

        $metadata = [
            'customer_id' => $invoice->customer_id,
            'branch_id' => $invoice->branch_id,
            'grand_total' => $invoice->grand_total_cents,
            'currency' => $invoice->currency,
        ];

        $this->outboxService->record(
            aggregateType: 'Invoice',
            aggregateId: $invoice->id,
            eventName: 'finance.invoice.paid.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new InvoicePaid(
            entityClass: Invoice::class,
            aggregateId: $invoice->id,
            aggregateVersion: 1,
            data: $eventData,
            metadata: $metadata
        ));
    }

    /**
     * Record invoice void event.
     */
    public function recordVoid(Invoice $invoice): void
    {
        $eventData = [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
        ];

        $metadata = [
            'customer_id' => $invoice->customer_id,
            'branch_id' => $invoice->branch_id,
        ];

        $this->outboxService->record(
            aggregateType: 'Invoice',
            aggregateId: $invoice->id,
            eventName: 'finance.invoice.voided.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new InvoiceVoided(
            entityClass: Invoice::class,
            aggregateId: $invoice->id,
            aggregateVersion: 1,
            data: $eventData,
            metadata: $metadata
        ));
    }
}
