<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Repositories;

use App\Modules\Inventory\Domain\Entities\Inventory;

interface InventoryWriteRepositoryInterface
{
    public function create(array $attributes): Inventory;

    public function update(string $id, array $attributes): ?Inventory;

    public function delete(string $id): bool;
}
