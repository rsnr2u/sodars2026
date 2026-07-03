<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Repositories;

use App\Modules\Inventory\Domain\Entities\InventoryAvailability;
use Illuminate\Support\Collection;

interface InventoryAvailabilityRepositoryInterface
{
    public function create(array $attributes): InventoryAvailability;

    public function delete(string $id): bool;

    public function findByFaceId(string $faceId): Collection;
}
