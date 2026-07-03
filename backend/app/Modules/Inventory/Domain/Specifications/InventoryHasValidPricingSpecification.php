<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use Illuminate\Database\Eloquent\Builder;

class InventoryHasValidPricingSpecification implements SpecificationInterface
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if ($candidate instanceof Inventory) {
            $faces = $candidate->faces;
            if ($faces->isEmpty()) {
                return false;
            }
            foreach ($faces as $face) {
                if (!$this->isFaceSatisfied($face)) {
                    return false;
                }
            }
            return true;
        }

        if ($candidate instanceof InventoryFace) {
            return $this->isFaceSatisfied($candidate);
        }

        return false;
    }

    protected function isFaceSatisfied(InventoryFace $face): bool
    {
        $now = now();
        return $face->pricings()
            ->where('effective_from', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
            })
            ->exists();
    }

    public function toQuery(Builder $builder): Builder
    {
        $now = now();
        return $builder->whereHas('faces', function ($q) use ($now) {
            $q->whereHas('pricings', function ($pq) use ($now) {
                $pq->where('effective_from', '<=', $now)
                    ->where(function ($eq) use ($now) {
                        $eq->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
                    });
            });
        });
    }
}
