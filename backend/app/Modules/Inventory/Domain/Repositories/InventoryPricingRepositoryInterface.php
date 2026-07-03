<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Repositories;

use App\Modules\Inventory\Domain\Entities\InventoryPricing;
use Illuminate\Support\Collection;

interface InventoryPricingRepositoryInterface
{
    public function create(array $attributes): InventoryPricing;

    public function delete(string $id): bool;

    public function findByFaceId(string $faceId): Collection;
}
