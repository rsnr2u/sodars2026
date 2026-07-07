<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Services;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignActivity;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

class CampaignLifecycleService
{
    public function __construct(
        protected OutboxService $outboxService
    ) {}

    public function recordCreation(Campaign $campaign, array $metadata = []): void
    {
        $eventData = $campaign->toArray();

        $this->outboxService->record(
            aggregateType: 'Campaign',
            aggregateId: $campaign->id,
            eventName: 'campaign.created.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new CampaignCreated(
            aggregateId: $campaign->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : null,
            metadata: $metadata
        ));

        $this->logActivity($campaign, 'Created', "Campaign created successfully.");
    }

    public function recordUpdate(Campaign $campaign, array $metadata = []): void
    {
        $eventData = $campaign->toArray();

        $this->outboxService->record(
            aggregateType: 'Campaign',
            aggregateId: $campaign->id,
            eventName: 'campaign.updated.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new CampaignUpdated(
            aggregateId: $campaign->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : null,
            metadata: $metadata
        ));

        $this->logActivity($campaign, 'Updated', "Campaign profile updated.");
    }

    public function transitionTo(Campaign $campaign, string $toStatus, array $metadata = []): void
    {
        $fromStatus = $campaign->status->value ?? $campaign->status;
        $allowed = CampaignStatus::allowedTransitions();

        if (!isset($allowed[$fromStatus]) || !in_array($toStatus, $allowed[$fromStatus], true)) {
            throw ValidationException::withMessages([
                'status' => ["Status transition from {$fromStatus} to {$toStatus} is not allowed."],
            ]);
        }

        $campaign->status = CampaignStatus::from($toStatus);
        $campaign->save();

        $eventData = [
            'campaign_id' => $campaign->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
        ];

        // Specific status outbox and domain events
        if ($toStatus === CampaignStatus::Approved->value) {
            $this->outboxService->record(
                aggregateType: 'Campaign',
                aggregateId: $campaign->id,
                eventName: 'campaign.approved.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );
            Event::dispatch(new CampaignApproved(
                aggregateId: $campaign->id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId(),
                traceId: TraceContext::traceId(),
                actorId: Auth::id() ? (string) Auth::id() : null,
                metadata: $metadata
            ));
            $this->logActivity($campaign, 'Approved', "Campaign status changed from {$fromStatus} to {$toStatus}.");
        } elseif ($toStatus === CampaignStatus::Scheduled->value) {
            $this->outboxService->record(
                aggregateType: 'Campaign',
                aggregateId: $campaign->id,
                eventName: 'campaign.scheduled.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );
            Event::dispatch(new CampaignScheduled(
                aggregateId: $campaign->id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId(),
                traceId: TraceContext::traceId(),
                actorId: Auth::id() ? (string) Auth::id() : null,
                metadata: $metadata
            ));
            $this->logActivity($campaign, 'Scheduled', "Campaign status changed from {$fromStatus} to {$toStatus}.");
        } elseif ($toStatus === CampaignStatus::Running->value) {
            $this->outboxService->record(
                aggregateType: 'Campaign',
                aggregateId: $campaign->id,
                eventName: 'campaign.started.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );
            Event::dispatch(new CampaignStarted(
                aggregateId: $campaign->id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId(),
                traceId: TraceContext::traceId(),
                actorId: Auth::id() ? (string) Auth::id() : null,
                metadata: $metadata
            ));
            $this->logActivity($campaign, 'Started', "Campaign status changed from {$fromStatus} to {$toStatus}.");
        } elseif ($toStatus === CampaignStatus::Paused->value) {
            $this->outboxService->record(
                aggregateType: 'Campaign',
                aggregateId: $campaign->id,
                eventName: 'campaign.paused.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );
            Event::dispatch(new CampaignPaused(
                aggregateId: $campaign->id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId(),
                traceId: TraceContext::traceId(),
                actorId: Auth::id() ? (string) Auth::id() : null,
                metadata: $metadata
            ));
            $this->logActivity($campaign, 'Paused', "Campaign status changed from {$fromStatus} to {$toStatus}.");
        } elseif ($toStatus === CampaignStatus::Completed->value) {
            $this->outboxService->record(
                aggregateType: 'Campaign',
                aggregateId: $campaign->id,
                eventName: 'campaign.completed.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );
            Event::dispatch(new CampaignCompleted(
                aggregateId: $campaign->id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId(),
                traceId: TraceContext::traceId(),
                actorId: Auth::id() ? (string) Auth::id() : null,
                metadata: $metadata
            ));
            $this->logActivity($campaign, 'Completed', "Campaign status changed from {$fromStatus} to {$toStatus}.");
        } elseif ($toStatus === CampaignStatus::Archived->value) {
            $this->outboxService->record(
                aggregateType: 'Campaign',
                aggregateId: $campaign->id,
                eventName: 'campaign.cancelled.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );
            Event::dispatch(new CampaignCancelled(
                aggregateId: $campaign->id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId(),
                traceId: TraceContext::traceId(),
                actorId: Auth::id() ? (string) Auth::id() : null,
                metadata: $metadata
            ));
            $this->logActivity($campaign, 'Archived', "Campaign status changed from {$fromStatus} to {$toStatus}.");
        } else {
            // Planning, Ready, etc.
            $this->recordUpdate($campaign, $metadata);
        }
    }

    public function recordCreativeAdded(Campaign $campaign, array $metadata = []): void
    {
        $this->outboxService->record(
            aggregateType: 'Campaign',
            aggregateId: $campaign->id,
            eventName: 'campaign.creative.added.v1',
            data: $metadata,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new CampaignCreativeAdded(
            aggregateId: $campaign->id,
            aggregateVersion: 1,
            data: $metadata,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : null,
            metadata: []
        ));

        $this->logActivity($campaign, 'CreativeAdded', "Creative artwork added: " . ($metadata['file_name'] ?? 'Asset ID ' . ($metadata['asset_id'] ?? '')));
    }

    public function recordCreativeRemoved(Campaign $campaign, array $metadata = []): void
    {
        $this->outboxService->record(
            aggregateType: 'Campaign',
            aggregateId: $campaign->id,
            eventName: 'campaign.creative.removed.v1',
            data: $metadata,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new CampaignCreativeRemoved(
            aggregateId: $campaign->id,
            aggregateVersion: 1,
            data: $metadata,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : null,
            metadata: []
        ));

        $this->logActivity($campaign, 'CreativeRemoved', "Creative artwork removed.");
    }

    public function recordProofUploaded(Campaign $campaign, array $metadata = []): void
    {
        $this->outboxService->record(
            aggregateType: 'Campaign',
            aggregateId: $campaign->id,
            eventName: 'campaign.proof.uploaded.v1',
            data: $metadata,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new CampaignProofUploaded(
            aggregateId: $campaign->id,
            aggregateVersion: 1,
            data: $metadata,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : null,
            metadata: []
        ));

        $this->logActivity($campaign, 'ProofUploaded', "Proof of execution uploaded.");
    }

    public function recordProofApproved(Campaign $campaign, array $metadata = []): void
    {
        $this->outboxService->record(
            aggregateType: 'Campaign',
            aggregateId: $campaign->id,
            eventName: 'campaign.proof.approved.v1',
            data: $metadata,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new CampaignProofApproved(
            aggregateId: $campaign->id,
            aggregateVersion: 1,
            data: $metadata,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : null,
            metadata: []
        ));

        $this->logActivity($campaign, 'ProofApproved', "Proof of execution approved.");
    }

    protected function logActivity(Campaign $campaign, string $action, string $description): void
    {
        CampaignActivity::create([
            'organization_id' => $campaign->organization_id,
            'campaign_id' => $campaign->id,
            'performed_by' => Auth::id() ? (string) Auth::id() : null,
            'event_name' => "campaign.{$action}",
            'action' => $action,
            'old_values' => [],
            'new_values' => ['description' => $description],
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'trace_id' => TraceContext::traceId(),
        ]);
    }
}
