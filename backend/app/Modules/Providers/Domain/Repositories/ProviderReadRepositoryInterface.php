<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Repositories;

use App\Modules\Providers\Domain\Entities\Provider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProviderReadRepositoryInterface
{
    public function find(string $id): ?Provider;

    public function findOrFail(string $id): Provider;

    public function findByUuid(string $uuid): ?Provider;

    public function findByRegNumber(string $regNum): ?Provider;

    public function findPendingVerification(): Collection;

    /**
     * Search and filter providers.
     *
     * @param array<string, mixed> $filters
     */
    public function searchProviders(string $term, array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
