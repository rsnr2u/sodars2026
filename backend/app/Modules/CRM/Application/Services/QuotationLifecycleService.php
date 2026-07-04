<?php

declare(strict_types=1);

namespace App\Modules\CRM\Application\Services;

use App\Core\Services\OutboxService;
use App\Modules\CRM\Domain\Entities\Quotation;
use App\Modules\CRM\Domain\Events\QuotationCreated;
use App\Modules\CRM\Domain\Events\QuotationStatusChanged;
use Illuminate\Support\Facades\Event;

class QuotationLifecycleService
{
    public function __construct(
        protected OutboxService $outboxService
    ) {}

    /**
     * Record quotation creation.
     */
    public function recordCreation(Quotation $quote): void
    {
        $eventData = [
            'quotation_id' => $quote->id,
            'quotation_number' => $quote->quotation_number,
            'status' => $quote->status instanceof \BackedEnum ? $quote->status->value : (string) $quote->status,
        ];

        $metadata = [
            'quotation_total' => 0,
            'currency' => 'INR',
            'assigned_user_id' => $quote->created_by,
        ];

        $this->outboxService->record(
            aggregateType: 'Quotation',
            aggregateId: $quote->id,
            eventName: 'crm.quotation.created.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new QuotationCreated(
            aggregateId: $quote->id,
            aggregateVersion: 1,
            data: $eventData,
            metadata: $metadata
        ));
    }

    /**
     * Record status updates.
     */
    public function recordStatusChange(Quotation $quote, string $fromStatus, string $toStatus): void
    {
        $eventData = [
            'quotation_id' => $quote->id,
            'quotation_number' => $quote->quotation_number,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'status' => $toStatus,
        ];

        $metadata = [
            'quotation_total' => 0,
            'currency' => 'INR',
            'assigned_user_id' => $quote->updated_by,
        ];

        $this->outboxService->record(
            aggregateType: 'Quotation',
            aggregateId: $quote->id,
            eventName: 'crm.quotation.status_changed.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new QuotationStatusChanged(
            aggregateId: $quote->id,
            aggregateVersion: 1,
            data: $eventData,
            metadata: $metadata
        ));
    }
}
