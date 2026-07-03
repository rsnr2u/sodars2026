<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Pipelines\Stages;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Providers\Domain\Entities\ProviderActivity;
use App\Modules\Providers\Domain\Events\ProviderRegistered;
use Closure;
use Illuminate\Support\Facades\Event;

class PublishEventsStage
{
    public function __construct(
        protected OutboxService $outboxService
    ) {}

    /**
     * Record registered event to outbox, dispatch domain event, and log activity timeline.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $provider = $passable['provider'];
        $user = $passable['user'];

        $eventData = [
            'provider_id' => $provider->id,
            'provider_code' => $provider->provider_code,
            'branch_id' => $provider->default_branch_id,
            'status' => $provider->status->value,
            'occurred_at' => now()->toIso8601String(),
            'trace' => [
                'correlation_id' => TraceContext::correlationId(),
                'causation_id' => TraceContext::causationId(),
                'trace_id' => TraceContext::traceId(),
            ]
        ];

        // 1. Record outbox event
        $this->outboxService->record(
            aggregateType: 'Provider',
            aggregateId: $provider->id,
            eventName: 'provider.registered.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 2. Dispatch domain event
        Event::dispatch(new ProviderRegistered(
            aggregateId: $provider->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            userId: $user?->id
        ));

        // 3. Log business timeline activity
        ProviderActivity::create([
            'provider_id' => $provider->id,
            'activity_type' => 'Registered',
            'description' => 'Provider account registered successfully with draft status.',
            'causation_id' => TraceContext::causationId(),
            'correlation_id' => TraceContext::correlationId(),
            'trace_id' => TraceContext::traceId(),
            'created_by' => $user?->id,
        ]);

        return $next($passable);
    }
}
