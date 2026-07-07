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
use App\Modules\Finance\Domain\Events\InvoiceCreated;
use App\Modules\Finance\Domain\Events\InvoiceIssued;
use App\Modules\Finance\Domain\Events\InvoicePaid;
use App\Modules\Finance\Domain\Events\InvoiceVoided;
use App\Modules\Finance\Domain\Events\PaymentReceived;
use App\Modules\Finance\Domain\Events\PaymentFailed;
use App\Modules\Providers\Domain\Events\ProviderCreated;
use App\Modules\Providers\Domain\Events\ProviderUpdated;
use App\Modules\Providers\Domain\Events\ProviderVerified;
use App\Modules\Providers\Domain\Events\ProviderSuspended;
use App\Modules\Providers\Domain\Events\ProviderSubscriptionChanged;
use App\Modules\Providers\Domain\Events\ProviderBankAccountUpdated;
use App\Modules\Providers\Domain\Events\ProviderDocumentUploaded;
use App\Modules\Campaigns\Domain\Events\CampaignCreated;
use App\Modules\Campaigns\Domain\Events\CampaignUpdated;
use App\Modules\Campaigns\Domain\Events\CampaignApproved;
use App\Modules\Campaigns\Domain\Events\CampaignScheduled;
use App\Modules\Campaigns\Domain\Events\CampaignStarted;
use App\Modules\Campaigns\Domain\Events\CampaignPaused;
use App\Modules\Campaigns\Domain\Events\CampaignCompleted;
use App\Modules\Campaigns\Domain\Events\CampaignCancelled;
use App\Modules\Campaigns\Domain\Events\CampaignCreativeAdded;
use App\Modules\Campaigns\Domain\Events\CampaignCreativeRemoved;
use App\Modules\Campaigns\Domain\Events\CampaignProofUploaded;
use App\Modules\Campaigns\Domain\Events\CampaignProofApproved;
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

    public function onInvoiceCreated(InvoiceCreated $event): void
    {
        $data = $event->data;
        $invoiceNumber = $data['invoice_number'] ?? 'ID: ' . $event->aggregateId;

        $envelope = AuditEnvelope::make('finance.invoice.created', "Invoice {$invoiceNumber} created")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onInvoiceIssued(InvoiceIssued $event): void
    {
        $data = $event->data;
        $invoiceNumber = $data['invoice_number'] ?? 'ID: ' . $event->aggregateId;

        $envelope = AuditEnvelope::make('finance.invoice.issued', "Invoice {$invoiceNumber} issued")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onInvoicePaid(InvoicePaid $event): void
    {
        $data = $event->data;
        $invoiceNumber = $data['invoice_number'] ?? 'ID: ' . $event->aggregateId;

        $envelope = AuditEnvelope::make('finance.invoice.paid', "Invoice {$invoiceNumber} fully paid")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onInvoiceVoided(InvoiceVoided $event): void
    {
        $data = $event->data;
        $invoiceNumber = $data['invoice_number'] ?? 'ID: ' . $event->aggregateId;

        $envelope = AuditEnvelope::make('finance.invoice.voided', "Invoice {$invoiceNumber} voided")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onPaymentReceived(PaymentReceived $event): void
    {
        $data = $event->data;
        $ref = $data['reference_number'] ?? 'ID: ' . $event->aggregateId;

        $envelope = AuditEnvelope::make('finance.payment.received', "Payment {$ref} received")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onPaymentFailed(PaymentFailed $event): void
    {
        $data = $event->data;
        $ref = $data['reference_number'] ?? 'ID: ' . $event->aggregateId;

        $envelope = AuditEnvelope::make('finance.payment.failed', "Payment {$ref} failed")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onProviderCreated(ProviderCreated $event): void
    {
        $data = $event->data;
        $name = $data['company_name'] ?? 'ID: ' . $event->aggregateId;

        $envelope = AuditEnvelope::make('provider.profile.created', "Provider {$name} profile created")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onProviderUpdated(ProviderUpdated $event): void
    {
        $data = $event->data;
        $name = $data['company_name'] ?? 'ID: ' . $event->aggregateId;

        $envelope = AuditEnvelope::make('provider.profile.updated', "Provider {$name} profile updated")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onProviderVerified(ProviderVerified $event): void
    {
        $data = $event->data;
        $name = $data['company_name'] ?? 'ID: ' . $event->aggregateId;

        $envelope = AuditEnvelope::make('provider.profile.verified', "Provider {$name} compliance profile verified")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onProviderSuspended(ProviderSuspended $event): void
    {
        $data = $event->data;
        $name = $data['company_name'] ?? 'ID: ' . $event->aggregateId;

        $envelope = AuditEnvelope::make('provider.profile.suspended', "Provider {$name} suspended")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onProviderSubscriptionChanged(ProviderSubscriptionChanged $event): void
    {
        $data = $event->data;
        $screens = $data['max_active_screens'] ?? 0;

        $envelope = AuditEnvelope::make('provider.subscription.changed', "Provider subscription updated (Screens limit: {$screens})")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onProviderBankAccountUpdated(ProviderBankAccountUpdated $event): void
    {
        $data = $event->data;
        $bank = $data['bank_name'] ?? 'unknown';

        $envelope = AuditEnvelope::make('provider.bank.updated', "Provider bank account details updated for {$bank}")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onProviderDocumentUploaded(ProviderDocumentUploaded $event): void
    {
        $data = $event->data;
        $type = $data['document_type'] ?? 'unknown';

        $envelope = AuditEnvelope::make('provider.documents.uploaded', "Provider document type {$type} uploaded")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onCampaignCreated(CampaignCreated $event): void
    {
        $data = $event->data;
        $name = $data['name'] ?? 'ID: ' . $event->aggregateId;

        $envelope = AuditEnvelope::make('campaign.profile.created', "Campaign {$name} created")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onCampaignUpdated(CampaignUpdated $event): void
    {
        $data = $event->data;
        $name = $data['name'] ?? 'ID: ' . $event->aggregateId;

        $envelope = AuditEnvelope::make('campaign.profile.updated', "Campaign {$name} details updated")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onCampaignApproved(CampaignApproved $event): void
    {
        $data = $event->data;
        $envelope = AuditEnvelope::make('campaign.profile.approved', "Campaign approved and ready")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onCampaignScheduled(CampaignScheduled $event): void
    {
        $data = $event->data;
        $envelope = AuditEnvelope::make('campaign.schedule.configured', "Campaign schedule configured and scheduled")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onCampaignStarted(CampaignStarted $event): void
    {
        $data = $event->data;
        $envelope = AuditEnvelope::make('campaign.profile.started', "Campaign execution started")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onCampaignPaused(CampaignPaused $event): void
    {
        $data = $event->data;
        $envelope = AuditEnvelope::make('campaign.profile.paused', "Campaign execution paused")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onCampaignCompleted(CampaignCompleted $event): void
    {
        $data = $event->data;
        $envelope = AuditEnvelope::make('campaign.profile.completed', "Campaign flight completed")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onCampaignCancelled(CampaignCancelled $event): void
    {
        $data = $event->data;
        $envelope = AuditEnvelope::make('campaign.profile.cancelled', "Campaign cancelled/archived")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onCampaignCreativeAdded(CampaignCreativeAdded $event): void
    {
        $data = $event->data;
        $envelope = AuditEnvelope::make('campaign.creatives.added', "Creative artwork added to campaign")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onCampaignCreativeRemoved(CampaignCreativeRemoved $event): void
    {
        $data = $event->data;
        $envelope = AuditEnvelope::make('campaign.creatives.removed', "Creative artwork removed/rejected from campaign")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onCampaignProofUploaded(CampaignProofUploaded $event): void
    {
        $data = $event->data;
        $envelope = AuditEnvelope::make('campaign.proofs.uploaded', "Execution proof uploaded to campaign")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onCampaignProofApproved(CampaignProofApproved $event): void
    {
        $data = $event->data;
        $envelope = AuditEnvelope::make('campaign.proofs.approved', "Execution proof verified and approved")
            ->actor($event->actorId ?? $event->userId ?? null, null)
            ->organization($event->organizationId ?? null)
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
            InvoiceCreated::class => 'onInvoiceCreated',
            InvoiceIssued::class => 'onInvoiceIssued',
            InvoicePaid::class => 'onInvoicePaid',
            InvoiceVoided::class => 'onInvoiceVoided',
            PaymentReceived::class => 'onPaymentReceived',
            PaymentFailed::class => 'onPaymentFailed',
            ProviderCreated::class => 'onProviderCreated',
            ProviderUpdated::class => 'onProviderUpdated',
            ProviderVerified::class => 'onProviderVerified',
            ProviderSuspended::class => 'onProviderSuspended',
            ProviderSubscriptionChanged::class => 'onProviderSubscriptionChanged',
            ProviderBankAccountUpdated::class => 'onProviderBankAccountUpdated',
            ProviderDocumentUploaded::class => 'onProviderDocumentUploaded',
            CampaignCreated::class => 'onCampaignCreated',
            CampaignUpdated::class => 'onCampaignUpdated',
            CampaignApproved::class => 'onCampaignApproved',
            CampaignScheduled::class => 'onCampaignScheduled',
            CampaignStarted::class => 'onCampaignStarted',
            CampaignPaused::class => 'onCampaignPaused',
            CampaignCompleted::class => 'onCampaignCompleted',
            CampaignCancelled::class => 'onCampaignCancelled',
            CampaignCreativeAdded::class => 'onCampaignCreativeAdded',
            CampaignCreativeRemoved::class => 'onCampaignCreativeRemoved',
            CampaignProofUploaded::class => 'onCampaignProofUploaded',
            CampaignProofApproved::class => 'onCampaignProofApproved',

            // Wallet Bounded Context Event Subscriptions
            \App\Modules\Wallet\Domain\Events\WalletCreated::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\WalletActivated::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\WalletSuspended::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\WalletDeposited::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\WalletTransferred::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\WalletAdjusted::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\WalletCredited::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\WalletDebited::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\SettlementCalculated::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\SettlementApproved::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\SettlementCredited::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\SettlementReversed::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\WithdrawalRequested::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\WithdrawalUnderReview::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\WithdrawalApproved::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\WithdrawalProcessing::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\WithdrawalCompleted::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\WithdrawalRejected::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\WithdrawalCancelled::class => 'onWalletEvent',
            \App\Modules\Wallet\Domain\Events\WithdrawalFailed::class => 'onWalletEvent',

            // Transport Bounded Context Event Subscriptions
            \App\Modules\Transport\Domain\Events\VehicleCreated::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\VehicleUpdated::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\VehicleStatusChanged::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\VehicleAssigned::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\VehicleReleased::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\MaintenanceScheduled::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\MaintenanceCompleted::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\VehicleFuelLogged::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\VehicleGPSLogged::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\DriverCreated::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\DriverUpdated::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\DriverSuspended::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\DriverLicenseExpiring::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\RouteCreated::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\RouteDispatched::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\RouteStatusChanged::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\RouteCompleted::class => 'onTransportEvent',
            \App\Modules\Transport\Domain\Events\RouteCancelled::class => 'onTransportEvent',

            // IoT Bounded Context
            \App\Modules\IoT\Domain\Events\DeviceRegistered::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceActivated::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceSuspended::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceAssigned::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceReleased::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceHeartbeatReceived::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceTelemetryReceived::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceTelemetryProcessed::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceOnlineDetected::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceOfflineDetected::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceCommandQueued::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceCommandDispatched::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceCommandAcknowledged::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceCommandCompleted::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceCommandFailed::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\FirmwarePublished::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\FirmwareInstalled::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\FirmwareRollback::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceAlertRaised::class => 'onIotEvent',
            \App\Modules\IoT\Domain\Events\DeviceAlertResolved::class => 'onIotEvent',

            // Operations Event Subscriptions
            \App\Modules\Operations\Domain\Events\ScheduleCreated::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ScheduleValidated::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ScheduleOptimized::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ScheduleAssigned::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ScheduleApproved::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ScheduleDispatched::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ScheduleStarted::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ScheduleCompleted::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ScheduleCancelled::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ScheduleDelayed::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ScheduleSuspended::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ScheduleFailed::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ResourceAssigned::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ResourceReleased::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ConflictDetected::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ConflictResolved::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ShiftStarted::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\ShiftCompleted::class => 'onOperationsEvent',
            \App\Modules\Operations\Domain\Events\CalendarUpdated::class => 'onOperationsEvent',
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

    public function onWalletEvent(object $event): void
    {
        $data = $event->data;
        $classBasename = class_basename($event);
        $eventType = match ($classBasename) {
            'WalletCreated' => 'wallet.profile.created',
            'WalletActivated' => 'wallet.profile.activated',
            'WalletSuspended' => 'wallet.profile.suspended',
            'WalletDeposited' => 'wallet.transactions.deposited',
            'WalletTransferred' => 'wallet.transactions.transferred',
            'WalletAdjusted' => 'wallet.transactions.adjusted',
            'WalletCredited' => 'wallet.transactions.credited',
            'WalletDebited' => 'wallet.transactions.debited',
            'SettlementCalculated' => 'wallet.settlements.calculated',
            'SettlementApproved' => 'wallet.settlements.approved',
            'SettlementCredited' => 'wallet.settlements.credited',
            'SettlementReversed' => 'wallet.settlements.reversed',
            'WithdrawalRequested' => 'wallet.withdrawals.requested',
            'WithdrawalUnderReview' => 'wallet.withdrawals.under_review',
            'WithdrawalApproved' => 'wallet.withdrawals.approved',
            'WithdrawalProcessing' => 'wallet.withdrawals.processing',
            'WithdrawalCompleted' => 'wallet.withdrawals.completed',
            'WithdrawalRejected' => 'wallet.withdrawals.rejected',
            'WithdrawalCancelled' => 'wallet.withdrawals.cancelled',
            'WithdrawalFailed' => 'wallet.withdrawals.failed',
            default => 'wallet.activity.other',
        };

        $envelope = AuditEnvelope::make($eventType, "Wallet activity: {$classBasename}")
            ->actor($event->actorId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onTransportEvent(object $event): void
    {
        $data = $event->data;
        $classBasename = class_basename($event);
        $eventType = match ($classBasename) {
            'VehicleCreated' => 'transport.vehicles.created',
            'VehicleUpdated' => 'transport.vehicles.updated',
            'VehicleStatusChanged' => 'transport.vehicles.status_changed',
            'VehicleAssigned' => 'transport.vehicles.assigned',
            'VehicleReleased' => 'transport.vehicles.released',
            'MaintenanceScheduled' => 'transport.vehicles.maintenance.scheduled',
            'MaintenanceCompleted' => 'transport.vehicles.maintenance.completed',
            'VehicleFuelLogged' => 'transport.vehicles.fuel_logged',
            'VehicleGPSLogged' => 'transport.vehicles.gps_logged',
            'DriverCreated' => 'transport.drivers.created',
            'DriverUpdated' => 'transport.drivers.updated',
            'DriverSuspended' => 'transport.drivers.suspended',
            'DriverLicenseExpiring' => 'transport.drivers.license_expiring',
            'RouteCreated' => 'transport.routes.created',
            'RouteDispatched' => 'transport.routes.dispatched',
            'RouteStatusChanged' => 'transport.routes.status_changed',
            'RouteCompleted' => 'transport.routes.completed',
            'RouteCancelled' => 'transport.routes.cancelled',
            default => 'transport.vehicles.other',
        };

        $envelope = AuditEnvelope::make($eventType, "Transport activity: {$classBasename}")
            ->actor($event->actorId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onIotEvent(object $event): void
    {
        $data = $event->data;
        $classBasename = class_basename($event);
        $eventType = match ($classBasename) {
            'DeviceRegistered' => 'iot.device.registered',
            'DeviceActivated' => 'iot.device.activated',
            'DeviceSuspended' => 'iot.device.suspended',
            'DeviceAssigned' => 'iot.assignment.assigned',
            'DeviceReleased' => 'iot.assignment.released',
            'DeviceHeartbeatReceived' => 'iot.telemetry.heartbeat',
            'DeviceTelemetryReceived', 'DeviceTelemetryProcessed' => 'iot.telemetry.processed',
            'DeviceOnlineDetected' => 'iot.device.online',
            'DeviceOfflineDetected' => 'iot.device.offline',
            'DeviceCommandQueued' => 'iot.command.queued',
            'DeviceCommandDispatched' => 'iot.command.dispatched',
            'DeviceCommandAcknowledged' => 'iot.command.acknowledged',
            'DeviceCommandCompleted' => 'iot.command.completed',
            'DeviceCommandFailed' => 'iot.command.failed',
            'FirmwarePublished' => 'iot.firmware.published',
            'FirmwareInstalled' => 'iot.firmware.installed',
            'FirmwareRollback' => 'iot.firmware.rollback',
            'DeviceAlertRaised' => 'iot.alert.raised',
            'DeviceAlertResolved' => 'iot.alert.resolved',
            default => 'iot.device.other',
        };

        $envelope = AuditEnvelope::make($eventType, "IoT activity: {$classBasename}")
            ->actor($event->actorId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }

    public function onOperationsEvent(object $event): void
    {
        $data = $event->data;
        $classBasename = class_basename($event);
        $eventType = match ($classBasename) {
            'ScheduleCreated' => 'operations.schedule.created',
            'ScheduleValidated' => 'operations.schedule.validated',
            'ScheduleOptimized' => 'operations.schedule.optimized',
            'ScheduleAssigned' => 'operations.schedule.assigned',
            'ScheduleApproved' => 'operations.schedule.approved',
            'ScheduleDispatched' => 'operations.schedule.dispatched',
            'ScheduleStarted' => 'operations.schedule.started',
            'ScheduleCompleted' => 'operations.schedule.completed',
            'ScheduleCancelled' => 'operations.schedule.cancelled',
            'ScheduleDelayed' => 'operations.schedule.delayed',
            'ScheduleSuspended' => 'operations.schedule.suspended',
            'ScheduleFailed' => 'operations.schedule.failed',
            'ResourceAssigned' => 'operations.resource.assigned',
            'ResourceReleased' => 'operations.resource.released',
            'ConflictDetected' => 'operations.conflict.detected',
            'ConflictResolved' => 'operations.conflict.resolved',
            'ShiftStarted' => 'operations.shift.started',
            'ShiftCompleted' => 'operations.shift.completed',
            'CalendarUpdated' => 'operations.calendar.updated',
            default => 'operations.schedule.other',
        };

        $envelope = AuditEnvelope::make($eventType, "Operations planning activity: {$classBasename}")
            ->actor($event->actorId ?? null, null)
            ->organization($event->organizationId ?? null)
            ->metadata($data);

        $this->logger->log($envelope);
    }
}
