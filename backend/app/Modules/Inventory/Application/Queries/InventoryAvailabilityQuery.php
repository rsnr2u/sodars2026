<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Queries;

use App\Modules\Inventory\Domain\Repositories\InventoryAvailabilityRepositoryInterface;
use Illuminate\Support\Collection;

class InventoryAvailabilityQuery
{
    public function __construct(
        protected InventoryAvailabilityRepositoryInterface $availabilityRepo
    ) {}

    /**
     * Retrieve availability list for face.
     */
    public function execute(string $faceId): Collection
    {
        return $this->availabilityRepo->findByFaceId($faceId);
    }
}
