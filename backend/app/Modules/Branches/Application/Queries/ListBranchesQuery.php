<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Queries;

use App\Modules\Branches\Application\DTOs\BranchFilterData;
use App\Modules\Branches\Domain\Repositories\BranchRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListBranchesQuery
{
    public function __construct(
        protected BranchRepositoryInterface $branchRepo
    ) {}

    /**
     * Retrieve a filtered and paginated list of branches.
     */
    public function execute(BranchFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        $term = $filters->search ?? '';
        return $this->branchRepo->searchBranches($term, $filters->toArray(), $perPage);
    }
}
