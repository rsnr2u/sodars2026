<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Queries;

use App\Modules\Inventory\Application\DTOs\InventoryFilterData;
use App\Modules\Inventory\Domain\Services\InventorySearchService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchInventoryQuery
{
    public function __construct(
        protected InventorySearchService $searchService
    ) {}

    /**
     * Search structures.
     */
    public function execute(InventoryFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->searchService->search($filters->toArray(), $perPage);
    }
}
