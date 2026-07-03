<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Repositories;

use App\Modules\Inventory\Domain\Entities\InventoryAvailability;
use App\Modules\Inventory\Domain\Repositories\InventoryAvailabilityRepositoryInterface;
use Illuminate\Support\Collection;

class InventoryAvailabilityRepository implements InventoryAvailabilityRepositoryInterface
{
    public function __construct(
        protected InventoryAvailability $model
    ) {}

    public function create(array $attributes): InventoryAvailability
    {
        return $this->model->create($attributes);
    }

    public function delete(string $id): bool
    {
        $availability = $this->model->find($id);
        if ($availability) {
            return (bool) $availability->delete();
        }
        return false;
    }

    public function findByFaceId(string $faceId): Collection
    {
        return $this->model->where('inventory_face_id', $faceId)->get();
    }
}
