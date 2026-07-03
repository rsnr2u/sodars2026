<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Repositories;

use App\Modules\Inventory\Domain\Entities\InventoryFace;
use Illuminate\Support\Collection;

interface InventoryFaceRepositoryInterface
{
    public function find(string $id): ?InventoryFace;

    public function findOrFail(string $id): InventoryFace;

    public function create(array $attributes): InventoryFace;

    public function update(string $id, array $attributes): ?InventoryFace;

    public function delete(string $id): bool;

    public function findByInventoryId(string $inventoryId): Collection;
}
