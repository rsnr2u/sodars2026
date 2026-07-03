<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Queries;

use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Branches\Domain\Repositories\BranchRepositoryInterface;

class GetBranchDetailsQuery
{
    public function __construct(
        protected BranchRepositoryInterface $branchRepo
    ) {}

    /**
     * Get branch details with eager loaded relationships.
     */
    public function execute(string $id): Branch
    {
        /** @var Branch $branch */
        $branch = $this->branchRepo->findOrFail($id);

        $branch->load([
            'members.user',
            'coverageAreas.country',
            'coverageAreas.state',
            'coverageAreas.district',
            'coverageAreas.city',
        ]);

        return $branch;
    }
}
