<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Branches\Application\DTOs\CoverageAreaData;
use App\Modules\Branches\Domain\Entities\BranchCoverageArea;
use App\Modules\Branches\Domain\Events\BranchCoverageAreaAdded;
use App\Modules\Branches\Domain\Repositories\BranchCoverageAreaRepositoryInterface;
use App\Modules\Branches\Domain\Repositories\BranchRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

class AddCoverageAreaAction
{
    public function __construct(
        protected BranchRepositoryInterface $branchRepo,
        protected BranchCoverageAreaRepositoryInterface $coverageAreaRepo,
        protected OutboxService $outboxService
    ) {}

    /**
     * Add a geographic coverage area to a branch.
     */
    public function execute(string $branchId, CoverageAreaData $data): BranchCoverageArea
    {
        // 1. Verify branch exists
        $this->branchRepo->findOrFail($branchId);

        // 2. Validate uniqueness of coverage
        if ($this->coverageAreaRepo->existsForBranch($branchId, $data->cityId)) {
            throw ValidationException::withMessages([
                'city_id' => ['This city is already configured as coverage under this branch.'],
            ]);
        }

        // 3. Persist the coverage area
        /** @var BranchCoverageArea $coverageArea */
        $coverageArea = $this->coverageAreaRepo->create([
            'branch_id' => $branchId,
            'country_id' => $data->countryId,
            'state_id' => $data->stateId,
            'district_id' => $data->districtId,
            'city_id' => $data->cityId,
        ]);

        $eventData = [
            'branch_id' => $branchId,
            'coverage_area_id' => $coverageArea->id,
            'country_id' => $data->countryId,
            'state_id' => $data->stateId,
            'district_id' => $data->districtId,
            'city_id' => $data->cityId,
        ];

        // 4. Record to outbox
        $this->outboxService->record(
            aggregateType: 'Branch',
            aggregateId: $branchId,
            eventName: 'branch.coverage_area.added.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 5. Dispatch domain event
        Event::dispatch(new BranchCoverageAreaAdded(
            aggregateId: $branchId,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            userId: Auth::id() ? (string) Auth::id() : null
        ));

        return $coverageArea;
    }
}
