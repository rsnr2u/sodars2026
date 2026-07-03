<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Pipelines\Stages;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Branches\Domain\Events\BranchCreated;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class PublishEventsStage
{
    public function __construct(
        protected OutboxService $outboxService
    ) {}

    /**
     * Record transactional outbox event and dispatch internal domain event.
     *
     * @param array{dto: \App\Modules\Branches\Application\DTOs\CreateBranchData, branch: \App\Modules\Branches\Domain\Entities\Branch} $passable
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $branch = $passable['branch'];

        $data = [
            'name' => $branch->name,
            'code' => $branch->code,
            'timezone' => $branch->timezone,
            'currency_code' => $branch->currency_code,
            'markup_percentage' => $branch->markup_percentage,
            'support_email' => $branch->support_email,
            'support_phone' => $branch->support_phone,
        ];

        // 1. Record transactional outbox event
        $this->outboxService->record(
            aggregateType: 'Branch',
            aggregateId: $branch->id,
            eventName: 'branch.created.v1',
            data: $data,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 2. Dispatch internal domain event
        Event::dispatch(new BranchCreated(
            aggregateId: $branch->id,
            aggregateVersion: 1,
            data: $data,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            userId: Auth::id() ? (string) Auth::id() : null
        ));

        return $next($passable);
    }
}
