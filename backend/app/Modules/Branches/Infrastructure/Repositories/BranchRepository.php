<?php

declare(strict_types=1);

namespace App\Modules\Branches\Infrastructure\Repositories;

use App\Core\Repositories\Eloquent\BaseRepository;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Branches\Domain\Repositories\BranchRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BranchRepository extends BaseRepository implements BranchRepositoryInterface
{
    public function __construct(Branch $model)
    {
        parent::__construct($model);
    }

    /**
     * Locate a branch by its unique short code.
     */
    public function findByCode(string $code): ?Branch
    {
        $branch = $this->model->where('code', $code)->first();
        return $branch instanceof Branch ? $branch : null;
    }

    /**
     * Check if a code is already in use.
     */
    public function existsByCode(string $code): bool
    {
        return $this->model->where('code', $code)->exists();
    }

    /**
     * Check if a name is already in use.
     */
    public function existsByName(string $name): bool
    {
        return $this->model->where('name', $name)->exists();
    }

    /**
     * Retrieve all active branches.
     */
    public function findActive(): Collection
    {
        return $this->model->active()->get();
    }

    /**
     * Retrieve inactive branches.
     */
    public function findInactive(): Collection
    {
        return $this->model->ofStatus('inactive')->get();
    }

    /**
     * Find branches associated with user.
     */
    public function findForUser(string $userId): Collection
    {
        return $this->model->whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId)->where('is_active', true);
        })->get();
    }

    /**
     * Search and filter branches using unified traits.
     */
    public function searchBranches(string $term, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if ($term !== '') {
            $query->search($term, ['name', 'code', 'support_email']);
        }

        if (!empty($filters)) {
            $query->filter($filters);
        }

        return $query->paginate($perPage);
    }
}
