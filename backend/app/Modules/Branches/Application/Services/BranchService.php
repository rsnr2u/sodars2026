<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Services;

use App\Core\Services\BaseService;
use App\Modules\Branches\Application\Actions\CreateBranchAction;
use App\Modules\Branches\Application\Actions\UpdateBranchAction;
use App\Modules\Branches\Application\Actions\ChangeBranchStatusAction;
use App\Modules\Branches\Application\Actions\AddCoverageAreaAction;
use App\Modules\Branches\Application\Actions\RemoveCoverageAreaAction;
use App\Modules\Branches\Application\Actions\AssignBranchMemberAction;
use App\Modules\Branches\Application\Actions\RemoveBranchMemberAction;
use App\Modules\Branches\Application\DTOs\CreateBranchData;
use App\Modules\Branches\Application\DTOs\UpdateBranchData;
use App\Modules\Branches\Application\DTOs\CoverageAreaData;
use App\Modules\Branches\Application\DTOs\BranchFilterData;
use App\Modules\Branches\Application\DTOs\BranchMemberData;
use App\Modules\Branches\Application\Queries\ListBranchesQuery;
use App\Modules\Branches\Application\Queries\GetBranchDetailsQuery;
use App\Modules\Branches\Application\Queries\SearchBranchesQuery;
use App\Modules\Branches\Application\Queries\BranchDashboardQuery;
use App\Modules\Branches\Application\Queries\BranchCoverageQuery;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Branches\Domain\Entities\BranchCoverageArea;
use App\Modules\Branches\Domain\Entities\BranchUser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BranchService extends BaseService
{
    public function __construct(
        protected CreateBranchAction $createBranchAction,
        protected UpdateBranchAction $updateBranchAction,
        protected ChangeBranchStatusAction $changeBranchStatusAction,
        protected AddCoverageAreaAction $addCoverageAreaAction,
        protected RemoveCoverageAreaAction $removeCoverageAreaAction,
        protected AssignBranchMemberAction $assignBranchMemberAction,
        protected RemoveBranchMemberAction $removeBranchMemberAction,
        protected ListBranchesQuery $listBranchesQuery,
        protected GetBranchDetailsQuery $getBranchDetailsQuery,
        protected SearchBranchesQuery $searchBranchesQuery,
        protected BranchDashboardQuery $branchDashboardQuery,
        protected BranchCoverageQuery $branchCoverageQuery
    ) {}

    /**
     * Create a branch.
     */
    public function createBranch(CreateBranchData $data): Branch
    {
        return $this->transaction(fn () => $this->createBranchAction->execute($data));
    }

    /**
     * Update branch profile details.
     */
    public function updateBranch(string $id, UpdateBranchData $data): Branch
    {
        return $this->transaction(fn () => $this->updateBranchAction->execute($id, $data));
    }

    /**
     * Transition status.
     */
    public function changeStatus(string $id, string $newStatus): Branch
    {
        return $this->transaction(fn () => $this->changeBranchStatusAction->execute($id, $newStatus));
    }

    /**
     * Add coverage city bounds.
     */
    public function addCoverageArea(string $branchId, CoverageAreaData $data): BranchCoverageArea
    {
        return $this->transaction(fn () => $this->addCoverageAreaAction->execute($branchId, $data));
    }

    /**
     * Remove coverage city bounds.
     */
    public function removeCoverageArea(string $branchId, string $areaId): void
    {
        $this->transaction(fn () => $this->removeCoverageAreaAction->execute($branchId, $areaId));
    }

    /**
     * Assign member user to branch.
     */
    public function assignMember(string $branchId, BranchMemberData $data): BranchUser
    {
        return $this->transaction(fn () => $this->assignBranchMemberAction->execute($branchId, $data));
    }

    /**
     * Mark membership as inactive.
     */
    public function removeMember(string $branchId, string $memberId): void
    {
        $this->transaction(fn () => $this->removeBranchMemberAction->execute($branchId, $memberId));
    }

    /**
     * List paginated branches.
     */
    public function list(BranchFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->listBranchesQuery->execute($filters, $perPage);
    }

    /**
     * Get branch detail profile.
     */
    public function getDetails(string $id): Branch
    {
        return $this->getBranchDetailsQuery->execute($id);
    }

    /**
     * Search branches.
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->searchBranchesQuery->execute($term, $perPage);
    }

    /**
     * Aggregate dashboard counters.
     */
    public function dashboard(string $id): array
    {
        return $this->branchDashboardQuery->execute($id);
    }

    /**
     * List branch coverage areas.
     */
    public function coverage(string $id): Collection
    {
        return $this->branchCoverageQuery->execute($id);
    }
}
