<?php

declare(strict_types=1);

namespace App\Modules\Providers\Infrastructure\Repositories;

use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProviderReadRepository implements ProviderReadRepositoryInterface
{
    public function __construct(
        protected Provider $model
    ) {}

    /**
     * Locate provider by primary key.
     */
    public function find(string $id): ?Provider
    {
        return $this->model->find($id);
    }

    /**
     * Locate provider or raise exception.
     */
    public function findOrFail(string $id): Provider
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Find by UUID.
     */
    public function findByUuid(string $uuid): ?Provider
    {
        return $this->model->where('id', $uuid)->first();
    }

    /**
     * Locate provider by registration number.
     */
    public function findByRegNumber(string $regNum): ?Provider
    {
        return $this->model->where('registration_number', $regNum)->first();
    }

    /**
     * Retrieve all pending verification accounts.
     */
    public function findPendingVerification(): Collection
    {
        return $this->model->where('status', 'pending')->get();
    }

    /**
     * Query search.
     */
    public function searchProviders(string $term, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if ($term !== '') {
            $query->search($term, ['company_name', 'registration_number', 'provider_code']);
        }

        if (!empty($filters)) {
            $query->filter($filters);
        }

        return $query->paginate($perPage);
    }
}
