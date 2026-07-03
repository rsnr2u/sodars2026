<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Repositories;

use App\Modules\Inventory\Domain\Entities\InventoryPricing;
use App\Modules\Inventory\Domain\Repositories\InventoryPricingRepositoryInterface;
use Illuminate\Support\Collection;

class InventoryPricingRepository implements InventoryPricingRepositoryInterface
{
    public function __construct(
        protected InventoryPricing $model
    ) {}

    public function create(array $attributes): InventoryPricing
    {
        return $this->model->create($attributes);
    }

    public function delete(string $id): bool
    {
        $pricing = $this->model->find($id);
        if ($pricing) {
            return (bool) $pricing->delete();
        }
        return false;
    }

    public function findByFaceId(string $faceId): Collection
    {
        return $this->model->where('inventory_face_id', $faceId)->get();
    }
}
