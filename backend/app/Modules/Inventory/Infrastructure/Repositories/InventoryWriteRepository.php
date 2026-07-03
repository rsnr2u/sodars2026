<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Repositories;

use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Repositories\InventoryWriteRepositoryInterface;

class InventoryWriteRepository implements InventoryWriteRepositoryInterface
{
    public function __construct(
        protected Inventory $model
    ) {}

    /**
     * Persist new aggregate root.
     */
    public function create(array $attributes): Inventory
    {
        return $this->model->create($attributes);
    }

    /**
     * Update existing aggregate root.
     */
    public function update(string $id, array $attributes): ?Inventory
    {
        $inventory = $this->model->find($id);
        if ($inventory) {
            $inventory->update($attributes);
            return $inventory;
        }
        return null;
    }

    /**
     * Soft delete aggregate root.
     */
    public function delete(string $id): bool
    {
        $inventory = $this->model->find($id);
        if ($inventory) {
            return (bool) $inventory->delete();
        }
        return false;
    }
}
