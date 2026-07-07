<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Services;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Events\ProviderCreated;
use App\Modules\Providers\Domain\Events\ProviderUpdated;
use App\Modules\Providers\Domain\Events\ProviderVerified;
use App\Modules\Providers\Domain\Events\ProviderSuspended;
use App\Modules\Providers\Domain\Events\ProviderActivated;
use App\Modules\Providers\Domain\Events\ProviderDeactivated;
use App\Modules\Providers\Domain\Events\ProviderSubscriptionChanged;
use App\Modules\Providers\Domain\Events\ProviderBankAccountUpdated;
use App\Modules\Providers\Domain\Events\ProviderDocumentUploaded;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

class ProviderLifecycleService
{
    public function __construct(
        protected OutboxService $outboxService
    ) {}

    public function recordCreation(Provider $provider, array $metadata = []): void
    {
        $eventData = $provider->toArray();

        $this->outboxService->record(
            aggregateType: 'Provider',
            aggregateId: $provider->id,
            eventName: 'provider.created.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new ProviderCreated(
            aggregateId: $provider->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            userId: Auth::id() ? (string) Auth::id() : null,
            metadata: $metadata
        ));
    }

    public function recordUpdate(Provider $provider, array $metadata = []): void
    {
        $eventData = $provider->toArray();

        $this->outboxService->record(
            aggregateType: 'Provider',
            aggregateId: $provider->id,
            eventName: 'provider.updated.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new ProviderUpdated(
            aggregateId: $provider->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            userId: Auth::id() ? (string) Auth::id() : null,
            metadata: $metadata
        ));
    }

    public function transitionTo(Provider $provider, string $toStatus, array $metadata = []): void
    {
        $fromStatus = $provider->status->value ?? $provider->status;
        $allowed = ProviderStatus::allowedTransitions();

        if (!isset($allowed[$fromStatus]) || !in_array($toStatus, $allowed[$fromStatus], true)) {
            throw ValidationException::withMessages([
                'status' => ["Status transition from {$fromStatus} to {$toStatus} is not allowed."],
            ]);
        }

        $provider->status = ProviderStatus::from($toStatus);
        $provider->save();

        $eventData = [
            'provider_id' => $provider->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
        ];

        // Specific status outbox and domain events
        if ($toStatus === ProviderStatus::Verified->value) {
            $this->outboxService->record(
                aggregateType: 'Provider',
                aggregateId: $provider->id,
                eventName: 'provider.verified.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );
            Event::dispatch(new ProviderVerified(
                aggregateId: $provider->id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId(),
                traceId: TraceContext::traceId(),
                userId: Auth::id() ? (string) Auth::id() : null,
                metadata: $metadata
            ));
        } elseif ($toStatus === ProviderStatus::Suspended->value) {
            $this->outboxService->record(
                aggregateType: 'Provider',
                aggregateId: $provider->id,
                eventName: 'provider.suspended.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );
            Event::dispatch(new ProviderSuspended(
                aggregateId: $provider->id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId(),
                traceId: TraceContext::traceId(),
                userId: Auth::id() ? (string) Auth::id() : null,
                metadata: $metadata
            ));
        } elseif ($toStatus === ProviderStatus::Active->value) {
            $this->outboxService->record(
                aggregateType: 'Provider',
                aggregateId: $provider->id,
                eventName: 'provider.activated.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );
            Event::dispatch(new ProviderActivated(
                aggregateId: $provider->id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId(),
                traceId: TraceContext::traceId(),
                userId: Auth::id() ? (string) Auth::id() : null,
                metadata: $metadata
            ));
        } elseif ($toStatus === ProviderStatus::Archived->value) {
            $this->outboxService->record(
                aggregateType: 'Provider',
                aggregateId: $provider->id,
                eventName: 'provider.archived.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );
            Event::dispatch(new ProviderDeactivated(
                aggregateId: $provider->id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId(),
                traceId: TraceContext::traceId(),
                userId: Auth::id() ? (string) Auth::id() : null,
                metadata: $metadata
            ));
        }
    }

    public function recordSubscriptionChange(Provider $provider, array $metadata = []): void
    {
        $sub = $provider->activeSubscription;
        $eventData = $sub ? $sub->toArray() : [];

        $this->outboxService->record(
            aggregateType: 'Provider',
            aggregateId: $provider->id,
            eventName: 'provider.subscription.changed.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new ProviderSubscriptionChanged(
            aggregateId: $provider->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            userId: Auth::id() ? (string) Auth::id() : null,
            metadata: $metadata
        ));
    }

    public function recordBankAccountUpdate(Provider $provider, array $metadata = []): void
    {
        $bank = $provider->primaryBankAccount;
        $eventData = $bank ? $bank->toArray() : [];

        $this->outboxService->record(
            aggregateType: 'Provider',
            aggregateId: $provider->id,
            eventName: 'provider.bank.updated.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new ProviderBankAccountUpdated(
            aggregateId: $provider->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            userId: Auth::id() ? (string) Auth::id() : null,
            metadata: $metadata
        ));
    }

    public function recordDocumentUpload(Provider $provider, array $metadata = []): void
    {
        $this->outboxService->record(
            aggregateType: 'Provider',
            aggregateId: $provider->id,
            eventName: 'provider.document.uploaded.v1',
            data: $metadata,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new ProviderDocumentUploaded(
            aggregateId: $provider->id,
            aggregateVersion: 1,
            data: $metadata,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            userId: Auth::id() ? (string) Auth::id() : null,
            metadata: []
        ));
    }
}
