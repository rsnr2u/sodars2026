<?php

declare(strict_types=1);

namespace App\Modules\Branches\Domain\Repositories;

use App\Core\Contracts\BaseRepositoryInterface;
use Illuminate\Support\Collection;

interface BranchCoverageAreaRepositoryInterface extends BaseRepositoryInterface
{
    public function findByBranch(string $branchId): Collection;

    public function existsForBranch(string $branchId, string $cityId): bool;
}
