<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Inventory\Domain\Entities\Inventory;
use Illuminate\Database\Eloquent\Builder;

class FaceAggregateValidationSpecification implements SpecificationInterface
{
    /**
     * Verify business rules on the aggregate faces.
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof Inventory) {
            return false;
        }

        $faces = $candidate->faces;
        
        // 1. Every inventory must contain at least one face (if approved or pending_approval)
        if (in_array($candidate->status->value, ['approved', 'pending_approval'], true)) {
            $activeFaces = $faces->filter(fn($f) => $f->is_active);
            if ($activeFaces->isEmpty()) {
                return false;
            }
        }

        // 2. Face codes must be unique within an inventory
        $faceCodes = $faces->pluck('face_code')->toArray();
        if (count($faceCodes) !== count(array_unique($faceCodes))) {
            return false;
        }

        // 3. Display order must be unique within an active inventory's faces
        $displayOrders = $faces->pluck('display_order')->toArray();
        if (count($displayOrders) !== count(array_unique($displayOrders))) {
            return false;
        }

        return true;
    }

    /**
     * Query representation (fallback builder).
     */
    public function toQuery(Builder $builder): Builder
    {
        return $builder;
    }
}
