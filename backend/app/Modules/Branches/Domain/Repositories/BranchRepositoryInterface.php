<?php

declare(strict_types=1);

namespace App\Modules\Branches\Domain\Repositories;

use App\Core\Contracts\BaseRepositoryInterface;
use App\Modules\Branches\Domain\Entities\Branch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface BranchRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code): ?Branch;

    public function existsByCode(string $code): bool;

    public function existsByName(string $name): bool;

    public function findActive(): Collection;

    public function findInactive(): Collection;

    public function findForUser(string $userId): Collection;

    /**
     * Search and filter branches.
     *
     * @param array<string, mixed> $filters
     */
    public function searchBranches(string $term, array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
