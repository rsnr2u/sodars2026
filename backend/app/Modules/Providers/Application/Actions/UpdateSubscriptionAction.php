<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Providers\Domain\Entities\ProviderSubscription;
use App\Modules\Providers\Domain\Entities\ProviderActivity;
use App\Modules\Providers\Domain\Events\ProviderSubscriptionChanged;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class UpdateSubscriptionAction
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerReadRepo,
        protected OutboxService $outboxService
    ) {}

    /**
     * Update active subscription plans and limits.
     */
    public function execute(string $providerId, ?string $planId, int $maxScreens, string $billingCycle): ProviderSubscription
    {
        $this->providerReadRepo->findOrFail($providerId);

        ProviderSubscription::where('provider_id', $providerId)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        /** @var ProviderSubscription $sub */
        $sub = ProviderSubscription::create([
            'provider_id' => $providerId,
            'subscription_plan_id' => $planId,
            'max_active_screens' => $maxScreens,
            'billing_cycle' => $billingCycle,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        $eventData = [
            'provider_id' => $providerId,
            'subscription_plan_id' => $planId,
            'max_active_screens' => $maxScreens,
            'billing_cycle' => $billingCycle,
        ];

        // 1. Record outbox
        $this->outboxService->record(
            aggregateType: 'Provider',
            aggregateId: $providerId,
            eventName: 'provider.subscription.changed.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 2. Dispatch event
        Event::dispatch(new ProviderSubscriptionChanged(
            aggregateId: $providerId,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            userId: Auth::id() ? (string) Auth::id() : null
        ));

        // 3. Log activity timeline
        ProviderActivity::create([
            'provider_id' => $providerId,
            'activity_type' => 'SubscriptionChanged',
            'description' => "Changed subscription tier: limit {$maxScreens} screens.",
            'causation_id' => TraceContext::causationId(),
            'correlation_id' => TraceContext::correlationId(),
            'trace_id' => TraceContext::traceId(),
            'created_by' => Auth::id() ? (string) Auth::id() : null,
        ]);

        return $sub;
    }
}
