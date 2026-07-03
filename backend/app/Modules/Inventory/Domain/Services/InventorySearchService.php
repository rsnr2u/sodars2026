<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Services;

use App\Modules\Inventory\Domain\Repositories\InventoryReadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InventorySearchService
{
    public function __construct(
        protected InventoryReadRepositoryInterface $readRepository
    ) {}

    /**
     * Search inventories using unified interface filters.
     *
     * @param array<string, mixed> $filters
     */
    public function search(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->readRepository->search($filters, $perPage);
    }

    /**
     * Find nearby structures within a radius.
     */
    public function findNearby(float $latitude, float $longitude, float $radiusKm, int $limit = 50): Collection
    {
        return $this->readRepository->findNearby($latitude, $longitude, $radiusKm, $limit);
    }
}
