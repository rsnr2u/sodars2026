<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Branches\Domain\Events\BranchCoverageAreaRemoved;
use App\Modules\Branches\Domain\Repositories\BranchCoverageAreaRepositoryInterface;
use App\Modules\Branches\Domain\Repositories\BranchRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class RemoveCoverageAreaAction
{
    public function __construct(
        protected BranchRepositoryInterface $branchRepo,
        protected BranchCoverageAreaRepositoryInterface $coverageAreaRepo,
        protected OutboxService $outboxService
    ) {}

    /**
     * Remove geographic coverage from a branch.
     */
    public function execute(string $branchId, string $areaId): void
    {
        $this->branchRepo->findOrFail($branchId);

        $coverageArea = $this->coverageAreaRepo->findOrFail($areaId);

        if ($coverageArea->branch_id !== $branchId) {
            throw new \InvalidArgumentException('The specified coverage area does not belong to this branch.');
        }

        $eventData = [
            'branch_id' => $branchId,
            'coverage_area_id' => $areaId,
            'city_id' => $coverageArea->city_id,
        ];

        $this->coverageAreaRepo->delete($areaId);

        // 1. Record outbox event
        $this->outboxService->record(
            aggregateType: 'Branch',
            aggregateId: $branchId,
            eventName: 'branch.coverage_area.removed.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 2. Dispatch domain event
        Event::dispatch(new BranchCoverageAreaRemoved(
            aggregateId: $branchId,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            userId: Auth::id() ? (string) Auth::id() : null
        ));
    }
}
