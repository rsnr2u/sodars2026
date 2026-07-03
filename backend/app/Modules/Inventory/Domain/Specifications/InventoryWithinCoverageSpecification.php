<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Inventory\Domain\Entities\Inventory;
use Illuminate\Database\Eloquent\Builder;

class InventoryWithinCoverageSpecification implements SpecificationInterface
{
    public function __construct(
        protected string $geoHashPrefix
    ) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof Inventory) {
            return false;
        }

        return str_starts_with($candidate->geo_hash, $this->geoHashPrefix);
    }

    public function toQuery(Builder $builder): Builder
    {
        return $builder->where('geo_hash', 'like', $this->geoHashPrefix . '%');
    }
}
