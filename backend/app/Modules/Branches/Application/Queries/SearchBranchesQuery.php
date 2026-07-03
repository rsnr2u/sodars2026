<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Queries;

use App\Modules\Branches\Domain\Repositories\BranchRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchBranchesQuery
{
    public function __construct(
        protected BranchRepositoryInterface $branchRepo
    ) {}

    /**
     * Search branches by term.
     */
    public function execute(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->branchRepo->searchBranches($term, [], $perPage);
    }
}
