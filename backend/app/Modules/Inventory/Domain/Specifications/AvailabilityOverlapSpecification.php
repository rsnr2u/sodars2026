<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Inventory\Domain\Entities\InventoryAvailability;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AvailabilityOverlapSpecification implements SpecificationInterface
{
    public function __construct(
        protected string $inventoryFaceId,
        protected Carbon $startAt,
        protected Carbon $endAt,
        protected ?string $excludeId = null
    ) {}

    /**
     * Check if candidate availability overlaps.
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof InventoryAvailability) {
            return false;
        }

        if ($candidate->inventory_face_id !== $this->inventoryFaceId) {
            return false;
        }

        if ($this->excludeId && $candidate->id === $this->excludeId) {
            return false;
        }

        return $candidate->start_at->lte($this->endAt) && $candidate->end_at->gte($this->startAt);
    }

    /**
     * Build the query that searches for overlapping availability records.
     */
    public function toQuery(Builder $builder): Builder
    {
        $query = $builder->where('inventory_face_id', $this->inventoryFaceId)
            ->where('start_at', '<=', $this->endAt->toDateTimeString())
            ->where('end_at', '>=', $this->startAt->toDateTimeString());

        if ($this->excludeId) {
            $query->where('id', '!=', $this->excludeId);
        }

        return $query;
    }
}
