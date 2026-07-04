<?php

declare(strict_types=1);

namespace App\Platform\Audit\Application\Listeners;

use App\Platform\Audit\Domain\Contracts\AuditLogger;
use App\Platform\Audit\Domain\ValueObjects\AuditEnvelope;
use App\Platform\Identity\Domain\Events\UserLoggedIn;
use App\Platform\Identity\Domain\Events\UserLoggedOut;
use App\Platform\Workflows\Domain\Events\WorkflowStarted;
use App\Platform\Workflows\Domain\Events\WorkflowCompleted;
use App\Platform\Workflows\Domain\Events\WorkflowCancelled;
use App\Modules\Bookings\Domain\Events\BookingCreated;
use App\Modules\Bookings\Domain\Events\BookingStatusChanged;
use App\Modules\Inventory\Domain\Events\InventoryCreated;
use App\Modules\Inventory\Domain\Events\InventoryStatusChanged;
use App\Modules\CRM\Domain\Events\LeadCreated;
use App\Modules\CRM\Domain\Events\LeadStatusChanged;
use App\Modules\CRM\Domain\Events\OpportunityCreated;
use App\Modules\CRM\Domain\Events\OpportunityStageChanged;
use App\Modules\CRM\Domain\Events\QuotationCreated;
use App\Modules\CRM\Domain\Events\QuotationStatusChanged;
use Illuminate\Contracts\Events\Dispatcher;

class AuditEventListener
{
    public function __construct(
        protected AuditLogger $logger
    ) {}

    public function onUserLogin(UserLoggedIn $event): void
    {
        $envelope = AuditEnvelope::make('user.login', "User logged in from IP {$event->ipAddress}")
            ->actor($event->userId, null);

        $envelope->ipAddress = $event->ipAddress;
        $envelope->userAgent = $event->userAgent;

        $this->logger->log($envelope);
    }

    public function onUserLogout(UserLoggedOut $event): void
    {
        $envelope = AuditEnvelope::make('user.logout', "User logged out")
            ->actor($event->userId, null);

        $this->logger->log($envelope);
    }

    public function onWorkflowStarted($event): void
    {
        // Dynamic event parsing to support loose mapping if workflow module isn't loaded or booted
        $workflowId = $event->workflowInstance->id ?? 'unknown';
        $definitionName = $event->workflowInstance->definition->name ?? 'unknown';

        $envelope = AuditEnvelope::make('workflow.started', "Workflow '{$definitionName}' (ID: {$workflowId}) started")
            ->organization($event->workflowInstance->organization_id ?? null)
            ->metadata([
                'workflow_instance_id' => $workflowId,
                'definition_name' => $definitionName,
            ]);

        $this->logger->log($envelope);
    }

    public function onWorkflowCompleted($event): void
    {
        $workflowId = $event->workflowInstance->id ?? 'unknown';
        $definitionName = $event->workflowInstance->definition->name ?? 'unknown';

        $envelope = AuditEnvelope::make('workflow.completed', "Workflow '{$definitionName}' (ID: {$workflowId}) completed successfully")
            ->organization($event->workflowInstance->organization_id ?? null)
            ->metadata([
                'workflow_instance_id' => $workflowId,
                'definition_name' => $definitionName,
            ]);

        $this->logger->log($envelope);
    }

    public function onBookingCreated(BookingCreated $event): void
    {
        $data = $event->data;
        $orgId = $data['organization_id'] ?? null;
        $bookingCode = $data['booking_code'] ?? 'ID: ' . $event->aggregateId;

        $envelope = AuditEnvelope::make('booking.created', "Booking {$bookingCode} created")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($orgId)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onBookingStatusChanged(BookingStatusChanged $event): void
    {
        $data = $event->data;
        $orgId = $data['organization_id'] ?? null;
        $toStatus = $data['to_status'] ?? 'unknown';
        $fromStatus = $data['from_status'] ?? 'unknown';
        $bookingCode = $data['booking_code'] ?? 'ID: ' . $event->aggregateId;

        $eventType = "booking.{$toStatus}";
        $description = "Booking {$bookingCode} transitioned from {$fromStatus} to {$toStatus}";
        if (!empty($data['comment'])) {
            $description .= " (Comment: {$data['comment']})";
        }

        $envelope = AuditEnvelope::make($eventType, $description)
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($orgId)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onInventoryCreated(InventoryCreated $event): void
    {
        $data = $event->data;
        $orgId = $data['organization_id'] ?? null;
        $code = $data['inventory_code'] ?? 'ID: ' . $event->aggregateId;

        $envelope = AuditEnvelope::make('inventory.created', "Inventory {$code} created")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($orgId)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onInventoryStatusChanged(InventoryStatusChanged $event): void
    {
        $data = $event->data;
        $orgId = $data['organization_id'] ?? null;
        $toStatus = $data['to_status'] ?? 'unknown';
        $fromStatus = $data['from_status'] ?? 'unknown';
        $code = $data['inventory_code'] ?? 'ID: ' . $event->aggregateId;

        $eventType = "inventory.{$toStatus}";
        $description = "Inventory {$code} transitioned from {$fromStatus} to {$toStatus}";
        if (!empty($data['comment'])) {
            $description .= " (Comment: {$data['comment']})";
        }

        $envelope = AuditEnvelope::make($eventType, $description)
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($orgId)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onLeadCreated(LeadCreated $event): void
    {
        $data = $event->data;
        $orgId = $event->organizationId;
        $title = $data['title'] ?? 'Lead';

        $envelope = AuditEnvelope::make('crm.lead.created', "Lead '{$title}' created")
            ->actor($event->actorId, null)
            ->organization($orgId)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onLeadStatusChanged(LeadStatusChanged $event): void
    {
        $data = $event->data;
        $orgId = $event->organizationId;
        $from = $data['from_status'] ?? 'unknown';
        $to = $data['to_status'] ?? 'unknown';
        $title = $data['title'] ?? 'Lead';

        $envelope = AuditEnvelope::make('crm.lead.status_changed', "Lead '{$title}' changed status from {$from} to {$to}")
            ->actor($event->actorId, null)
            ->organization($orgId)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onOpportunityCreated(OpportunityCreated $event): void
    {
        $data = $event->data;
        $orgId = $event->organizationId;
        $title = $data['title'] ?? 'Opportunity';

        $envelope = AuditEnvelope::make('crm.opportunity.created', "Opportunity '{$title}' created")
            ->actor($event->actorId, null)
            ->organization($orgId)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onOpportunityStageChanged(OpportunityStageChanged $event): void
    {
        $data = $event->data;
        $orgId = $event->organizationId;
        $from = $data['from_pipeline_stage_id'] ?? 'unknown';
        $to = $data['to_pipeline_stage_id'] ?? 'unknown';
        $title = $data['title'] ?? 'Opportunity';

        $envelope = AuditEnvelope::make('crm.opportunity.stage_changed', "Opportunity '{$title}' stage updated from {$from} to {$to}")
            ->actor($event->actorId, null)
            ->organization($orgId)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onQuotationCreated(QuotationCreated $event): void
    {
        $data = $event->data;
        $orgId = $event->organizationId;
        $num = $data['quotation_number'] ?? 'unknown';

        $envelope = AuditEnvelope::make('crm.quotation.created', "Quotation {$num} created")
            ->actor($event->actorId, null)
            ->organization($orgId)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onQuotationStatusChanged(QuotationStatusChanged $event): void
    {
        $data = $event->data;
        $orgId = $event->organizationId;
        $from = $data['from_status'] ?? 'unknown';
        $to = $data['to_status'] ?? 'unknown';
        $num = $data['quotation_number'] ?? 'unknown';

        $envelope = AuditEnvelope::make('crm.quotation.status_changed', "Quotation {$num} status changed from {$from} to {$to}")
            ->actor($event->actorId, null)
            ->organization($orgId)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function subscribe(Dispatcher $events): array
    {
        $subs = [
            UserLoggedIn::class => 'onUserLogin',
            UserLoggedOut::class => 'onUserLogout',
            BookingCreated::class => 'onBookingCreated',
            BookingStatusChanged::class => 'onBookingStatusChanged',
            InventoryCreated::class => 'onInventoryCreated',
            InventoryStatusChanged::class => 'onInventoryStatusChanged',
            LeadCreated::class => 'onLeadCreated',
            LeadStatusChanged::class => 'onLeadStatusChanged',
            OpportunityCreated::class => 'onOpportunityCreated',
            OpportunityStageChanged::class => 'onOpportunityStageChanged',
            QuotationCreated::class => 'onQuotationCreated',
            QuotationStatusChanged::class => 'onQuotationStatusChanged',
        ];

        // Register workflow events if classes exist
        if (class_exists(WorkflowStarted::class)) {
            $subs[WorkflowStarted::class] = 'onWorkflowStarted';
        }
        if (class_exists(WorkflowCompleted::class)) {
            $subs[WorkflowCompleted::class] = 'onWorkflowCompleted';
        }

        return $subs;
    }
}
