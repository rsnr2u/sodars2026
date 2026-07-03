<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Repositories;

use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Repositories\InventoryReadRepositoryInterface;
use App\Modules\Inventory\Domain\ValueObjects\GeoLocation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InventoryReadRepository implements InventoryReadRepositoryInterface
{
    public function __construct(
        protected Inventory $model
    ) {}

    /**
     * Locate inventory by primary key.
     */
    public function find(string $id): ?Inventory
    {
        return $this->model->find($id);
    }

    /**
     * Locate inventory or raise exception.
     */
    public function findOrFail(string $id): Inventory
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Locate inventory by human-readable code.
     */
    public function findByCode(string $code): ?Inventory
    {
        return $this->model->where('inventory_code', $code)->first();
    }

    /**
     * Search and filter inventories with pagination.
     *
     * @param array<string, mixed> $filters
     */
    public function search(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('inventory_code', 'LIKE', "%{$term}%")
                  ->orWhere('display_name', 'LIKE', "%{$term}%")
                  ->orWhere('normalized_address', 'LIKE', "%{$term}%")
                  ->orWhere('search_keywords', 'LIKE', "%{$term}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['inventory_category'])) {
            $query->where('inventory_category', $filters['inventory_category']);
        }

        if (!empty($filters['inventory_type'])) {
            $query->where('inventory_type', $filters['inventory_type']);
        }

        if (!empty($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (!empty($filters['state_id'])) {
            $query->where('state_id', $filters['state_id']);
        }

        if (!empty($filters['ownership_type'])) {
            $query->where('ownership_type', $filters['ownership_type']);
        }

        if (isset($filters['marketplace_enabled'])) {
            $query->where('marketplace_enabled', (bool) $filters['marketplace_enabled']);
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', (bool) $filters['is_featured']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get nearby structures using geohash prefix matching and Haversine distance.
     */
    public function findNearby(float $latitude, float $longitude, float $radiusKm, int $limit = 50): Collection
    {
        // Use geohash prefix for fast pre-filtering, then refine with Haversine
        $geoLocation = new GeoLocation($latitude, $longitude);
        $prefix = substr($geoLocation->geoHash, 0, 5); // ~5km precision

        $earthRadiusKm = 6371;

        return $this->model
            ->where('geo_hash', 'LIKE', "{$prefix}%")
            ->selectRaw("*, (
                {$earthRadiusKm} * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance_km", [$latitude, $longitude, $latitude])
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km')
            ->limit($limit)
            ->get();
    }
}
