<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Pipelines\Stages;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Campaigns\Domain\Events\CampaignCreated;
use App\Modules\Campaigns\Domain\Entities\CampaignActivity;
use Closure;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class PublishEventsStage
{
    public function __construct(
        protected OutboxService $outboxService
    ) {}

    public function handle(array $passable, Closure $next): mixed
    {
        $campaign = $passable['campaign'];

        $eventData = [
            'campaign_id' => $campaign->id,
            'campaign_code' => $campaign->campaign_code,
            'customer_id' => $campaign->customer_id,
            'branch_id' => $campaign->branch_id,
            'start_date' => $campaign->start_date->toDateString(),
            'end_date' => $campaign->end_date->toDateString(),
            'status' => $campaign->status->value,
        ];

        // 1. Transactional Outbox
        $this->outboxService->record(
            aggregateType: 'Campaign',
            aggregateId: $campaign->id,
            eventName: 'campaign.created.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 2. Dispatch Local Domain Event
        Event::dispatch(new CampaignCreated(
            aggregateId: $campaign->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId() ?? (string) Str::uuid(),
            traceId: TraceContext::traceId() ?? (string) Str::uuid()
        ));

        // 3. Activity Timeline entry
        CampaignActivity::create([
            'id' => (string) Str::uuid(),
            'campaign_id' => $campaign->id,
            'performed_by' => auth()->id() ?? $campaign->customer_id,
            'event_name' => 'campaign.created.v1',
            'action' => 'Created',
            'old_values' => null,
            'new_values' => $campaign->toArray(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
        ]);

        return $next($passable);
    }
}
