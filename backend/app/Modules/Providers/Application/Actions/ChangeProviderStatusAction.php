<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Core\Services\StateService;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderActivity;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Domain\Events\ProviderSuspended;
use App\Modules\Providers\Domain\Events\ProviderVerified;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;
use App\Modules\Providers\Domain\Repositories\ProviderWriteRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class ChangeProviderStatusAction
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerReadRepo,
        protected ProviderWriteRepositoryInterface $providerWriteRepo,
        protected StateService $stateService,
        protected OutboxService $outboxService
    ) {}

    /**
     * Transition provider operational status.
     */
    public function execute(string $providerId, string $newStatus): Provider
    {
        /** @var Provider $provider */
        $provider = $this->providerReadRepo->findOrFail($providerId);

        $currentStatusVal = $provider->status instanceof ProviderStatus ? $provider->status->value : (string) $provider->status;

        // Perform state transition check
        $this->stateService->validateTransition(
            $currentStatusVal,
            $newStatus,
            ProviderStatus::allowedTransitions()
        );

        $this->providerWriteRepo->update($providerId, ['status' => $newStatus]);

        /** @var Provider $updated */
        $updated = $this->providerReadRepo->findOrFail($providerId);

        $eventData = [
            'provider_id' => $providerId,
            'provider_code' => $updated->provider_code,
            'status' => $newStatus,
        ];

        $eventName = $newStatus === 'verified' 
            ? 'provider.verified.v1' 
            : ($newStatus === 'suspended' ? 'provider.suspended.v1' : 'provider.status_changed.v1');

        // 1. Record outbox
        $this->outboxService->record(
            aggregateType: 'Provider',
            aggregateId: $providerId,
            eventName: $eventName,
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 2. Dispatch events
        if ($newStatus === 'verified') {
            Event::dispatch(new ProviderVerified(
                aggregateId: $providerId,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId(),
                traceId: TraceContext::traceId(),
                userId: Auth::id() ? (string) Auth::id() : null
            ));
        } elseif ($newStatus === 'suspended') {
            Event::dispatch(new ProviderSuspended(
                aggregateId: $providerId,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId(),
                traceId: TraceContext::traceId(),
                userId: Auth::id() ? (string) Auth::id() : null
            ));
        }

        // 3. Log activity
        ProviderActivity::create([
            'provider_id' => $providerId,
            'activity_type' => ucfirst($newStatus),
            'description' => "Changed provider status from [{$currentStatusVal}] to [{$newStatus}].",
            'causation_id' => TraceContext::causationId(),
            'correlation_id' => TraceContext::correlationId(),
            'trace_id' => TraceContext::traceId(),
            'created_by' => Auth::id() ? (string) Auth::id() : null,
        ]);

        return $updated;
    }
}
