<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Queries;

use App\Modules\Inventory\Application\DTOs\InventoryFilterData;
use App\Modules\Inventory\Domain\Repositories\InventoryReadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListInventoryQuery
{
    public function __construct(
        protected InventoryReadRepositoryInterface $readRepo
    ) {}

    /**
     * Retrieve list of structures.
     */
    public function execute(InventoryFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->readRepo->search($filters->toArray(), $perPage);
    }
}
