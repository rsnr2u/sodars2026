<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Enums\InventoryStatus;
use Illuminate\Database\Eloquent\Builder;

class InventoryReadyForBookingSpecification implements SpecificationInterface
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof Inventory) {
            return false;
        }

        return $candidate->status === InventoryStatus::Approved
            && $candidate->marketplace_enabled
            && $candidate->faces()->where('is_active', true)->exists();
    }

    public function toQuery(Builder $builder): Builder
    {
        return $builder->where('status', InventoryStatus::Approved->value)
            ->where('marketplace_enabled', true)
            ->whereHas('faces', function ($query) {
                $query->where('is_active', true);
            });
    }
}
