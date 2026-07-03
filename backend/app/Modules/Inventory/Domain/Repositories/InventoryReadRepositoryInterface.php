<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Repositories;

use App\Modules\Inventory\Domain\Entities\Inventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface InventoryReadRepositoryInterface
{
    public function find(string $id): ?Inventory;

    public function findOrFail(string $id): Inventory;

    public function findByCode(string $code): ?Inventory;

    /**
     * Search and filter inventories.
     *
     * @param array<string, mixed> $filters
     */
    public function search(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get nearby structures using radius query.
     */
    public function findNearby(float $latitude, float $longitude, float $radiusKm, int $limit = 50): Collection;
}
