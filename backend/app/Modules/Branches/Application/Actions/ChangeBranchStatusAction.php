<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Core\Services\StateService;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Branches\Domain\Enums\BranchStatus;
use App\Modules\Branches\Domain\Events\BranchStatusChanged;
use App\Modules\Branches\Domain\Repositories\BranchRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class ChangeBranchStatusAction
{
    public function __construct(
        protected BranchRepositoryInterface $branchRepo,
        protected StateService $stateService,
        protected OutboxService $outboxService
    ) {}

    /**
     * Transition the branch status following enum constraints.
     */
    public function execute(string $id, string $newStatus): Branch
    {
        /** @var Branch $branch */
        $branch = $this->branchRepo->findOrFail($id);

        $currentStatusVal = $branch->status instanceof BranchStatus ? $branch->status->value : (string) $branch->status;

        // Perform transition check via StateService
        $this->stateService->validateTransition(
            $currentStatusVal,
            $newStatus,
            BranchStatus::allowedTransitions()
        );

        $this->branchRepo->update($id, ['status' => $newStatus]);

        /** @var Branch $updatedBranch */
        $updatedBranch = $this->branchRepo->findOrFail($id);

        $eventData = [
            'id' => $id,
            'old_status' => $currentStatusVal,
            'new_status' => $newStatus,
        ];

        // 1. Record outbox event
        $this->outboxService->record(
            aggregateType: 'Branch',
            aggregateId: $id,
            eventName: 'branch.status_changed.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 2. Dispatch domain event
        Event::dispatch(new BranchStatusChanged(
            aggregateId: $id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            userId: Auth::id() ? (string) Auth::id() : null
        ));

        return $updatedBranch;
    }
}
