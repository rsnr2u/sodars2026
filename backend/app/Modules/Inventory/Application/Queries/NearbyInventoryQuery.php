<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Queries;

use App\Modules\Inventory\Domain\Services\InventorySearchService;
use Illuminate\Support\Collection;

class NearbyInventoryQuery
{
    public function __construct(
        protected InventorySearchService $searchService
    ) {}

    /**
     * Retrieve structures within geographic radius.
     */
    public function execute(float $lat, float $lon, float $radiusKm, int $limit = 50): Collection
    {
        return $this->searchService->findNearby($lat, $lon, $radiusKm, $limit);
    }
}
