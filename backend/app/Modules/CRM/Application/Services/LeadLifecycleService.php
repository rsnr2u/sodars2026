<?php

declare(strict_types=1);

namespace App\Modules\CRM\Application\Services;

use App\Core\Services\OutboxService;
use App\Modules\CRM\Domain\Entities\Lead;
use App\Modules\CRM\Domain\Events\LeadCreated;
use App\Modules\CRM\Domain\Events\LeadStatusChanged;
use Illuminate\Support\Facades\Event;

class LeadLifecycleService
{
    public function __construct(
        protected OutboxService $outboxService
    ) {}

    /**
     * Record lead creation in outbox and fire domain event.
     */
    public function recordCreation(Lead $lead): void
    {
        $eventData = [
            'lead_id' => $lead->id,
            'title' => $lead->title,
            'source' => $lead->source,
            'status' => $lead->status instanceof \BackedEnum ? $lead->status->value : (string) $lead->status,
        ];

        $metadata = [
            'lead_source' => $lead->source,
            'assigned_user_id' => $lead->assigned_to,
        ];

        $this->outboxService->record(
            aggregateType: 'Lead',
            aggregateId: $lead->id,
            eventName: 'crm.lead.created.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new LeadCreated(
            aggregateId: $lead->id,
            aggregateVersion: 1,
            data: $eventData,
            metadata: $metadata
        ));
    }

    /**
     * Record status changes in outbox and fire domain event.
     */
    public function recordStatusChange(Lead $lead, string $fromStatus, string $toStatus): void
    {
        $eventData = [
            'lead_id' => $lead->id,
            'title' => $lead->title,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'status' => $toStatus,
        ];

        $metadata = [
            'lead_source' => $lead->source,
            'assigned_user_id' => $lead->assigned_to,
        ];

        $this->outboxService->record(
            aggregateType: 'Lead',
            aggregateId: $lead->id,
            eventName: 'crm.lead.status_changed.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new LeadStatusChanged(
            aggregateId: $lead->id,
            aggregateVersion: 1,
            data: $eventData,
            metadata: $metadata
        ));
    }
}
