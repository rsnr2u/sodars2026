<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Branches\Application\DTOs\UpdateBranchData;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Branches\Domain\Events\BranchUpdated;
use App\Modules\Branches\Domain\Repositories\BranchRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

class UpdateBranchAction
{
    public function __construct(
        protected BranchRepositoryInterface $branchRepo,
        protected OutboxService $outboxService
    ) {}

    /**
     * Update an existing branch profile.
     */
    public function execute(string $id, UpdateBranchData $data): Branch
    {
        /** @var Branch $branch */
        $branch = $this->branchRepo->findOrFail($id);

        if ($data->markupPercentage !== null && ($data->markupPercentage < 0 || $data->markupPercentage > 20)) {
            throw ValidationException::withMessages([
                'markup_percentage' => ['Markup percentage must be between 0 and 20.'],
            ]);
        }

        $attributes = $data->toArray();
        $this->branchRepo->update($id, $attributes);

        /** @var Branch $updatedBranch */
        $updatedBranch = $this->branchRepo->findOrFail($id);

        $eventData = [
            'id' => $id,
            'name' => $updatedBranch->name,
            'timezone' => $updatedBranch->timezone,
            'currency_code' => $updatedBranch->currency_code,
            'markup_percentage' => $updatedBranch->markup_percentage,
            'support_email' => $updatedBranch->support_email,
            'support_phone' => $updatedBranch->support_phone,
        ];

        // 1. Record outbox event
        $this->outboxService->record(
            aggregateType: 'Branch',
            aggregateId: $id,
            eventName: 'branch.updated.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 2. Dispatch domain event
        Event::dispatch(new BranchUpdated(
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
