<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Repositories;

use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Inventory\Domain\Repositories\InventoryFaceRepositoryInterface;
use Illuminate\Support\Collection;

class InventoryFaceRepository implements InventoryFaceRepositoryInterface
{
    public function __construct(
        protected InventoryFace $model
    ) {}

    public function find(string $id): ?InventoryFace
    {
        return $this->model->find($id);
    }

    public function findOrFail(string $id): InventoryFace
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $attributes): InventoryFace
    {
        return $this->model->create($attributes);
    }

    public function update(string $id, array $attributes): ?InventoryFace
    {
        $face = $this->model->find($id);
        if ($face) {
            $face->update($attributes);
            return $face;
        }
        return null;
    }

    public function delete(string $id): bool
    {
        $face = $this->model->find($id);
        if ($face) {
            return (bool) $face->delete();
        }
        return false;
    }

    public function findByInventoryId(string $inventoryId): Collection
    {
        return $this->model->where('inventory_id', $inventoryId)->get();
    }
}
