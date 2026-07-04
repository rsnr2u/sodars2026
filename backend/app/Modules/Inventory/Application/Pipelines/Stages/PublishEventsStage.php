<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Pipelines\Stages;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Inventory\Domain\Entities\InventoryActivity;
use App\Modules\Inventory\Domain\Events\InventoryCreated;
use Closure;
use Illuminate\Support\Facades\Event;

class PublishEventsStage
{
    public function __construct(
        protected OutboxService $outboxService
    ) {}

    /**
     * Record outbox events, dispatch domain events, and log activities.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $inventory = $passable['inventory'];

        $eventData = [
            'inventory_id' => $inventory->id,
            'inventory_code' => $inventory->inventory_code,
            'organization_id' => $inventory->organization_id,
            'provider_id' => $inventory->provider_id,
            'branch_id' => $inventory->branch_id,
            'status' => $inventory->status->value,
            'occurred_at' => now()->toIso8601String(),
            'trace' => [
                'correlation_id' => TraceContext::correlationId(),
                'causation_id' => TraceContext::causationId(),
                'trace_id' => TraceContext::traceId(),
            ]
        ];

        // 1. Record outbox event
        $this->outboxService->record(
            aggregateType: 'Inventory',
            aggregateId: $inventory->id,
            eventName: 'inventory.created.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 2. Dispatch domain event
        Event::dispatch(new InventoryCreated(
            aggregateId: $inventory->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId() ?? (string) \Illuminate\Support\Str::uuid(),
            traceId: TraceContext::traceId() ?? (string) \Illuminate\Support\Str::uuid()
        ));

        // 3. Log business timeline activity
        InventoryActivity::create([
            'inventory_id' => $inventory->id,
            'performed_by' => auth()->id(),
            'event_name' => 'inventory.created.v1',
            'action' => 'Created',
            'old_values' => null,
            'new_values' => $inventory->toArray(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'trace_id' => TraceContext::traceId(),
        ]);

        return $next($passable);
    }
}
