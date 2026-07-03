<?php

declare(strict_types=1);

namespace App\Modules\Branches\Infrastructure\Repositories;

use App\Core\Repositories\Eloquent\BaseRepository;
use App\Modules\Branches\Domain\Entities\BranchCoverageArea;
use App\Modules\Branches\Domain\Repositories\BranchCoverageAreaRepositoryInterface;
use Illuminate\Support\Collection;

class BranchCoverageAreaRepository extends BaseRepository implements BranchCoverageAreaRepositoryInterface
{
    public function __construct(BranchCoverageArea $model)
    {
        parent::__construct($model);
    }

    /**
     * Retrieve all coverage areas for a specific branch.
     */
    public function findByBranch(string $branchId): Collection
    {
        return $this->model->where('branch_id', $branchId)->get();
    }

    /**
     * Check if a specific city is already assigned.
     */
    public function existsForBranch(string $branchId, string $cityId): bool
    {
        return $this->model->where('branch_id', $branchId)->where('city_id', $cityId)->exists();
    }
}
