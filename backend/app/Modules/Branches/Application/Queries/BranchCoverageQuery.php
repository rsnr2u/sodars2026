<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Queries;

use App\Modules\Branches\Domain\Repositories\BranchCoverageAreaRepositoryInterface;
use Illuminate\Support\Collection;

class BranchCoverageQuery
{
    public function __construct(
        protected BranchCoverageAreaRepositoryInterface $coverageAreaRepo
    ) {}

    /**
     * Get branch coverage areas.
     */
    public function execute(string $branchId): Collection
    {
        $areas = $this->coverageAreaRepo->findByBranch($branchId);

        $areas->load([
            'country',
            'state',
            'district',
            'city',
        ]);

        return $areas;
    }
}
