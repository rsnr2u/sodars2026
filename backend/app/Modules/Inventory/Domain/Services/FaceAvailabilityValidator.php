<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Services;

use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Inventory\Domain\Specifications\InventoryReadyForBookingSpecification;
use App\Modules\Inventory\Domain\Specifications\InventoryHasValidPricingSpecification;
use App\Modules\Inventory\Domain\Specifications\InventoryHasActiveSubscriptionSpecification;
use App\Modules\Inventory\Domain\Specifications\AvailabilityOverlapSpecification;
use Carbon\Carbon;

class FaceAvailabilityValidator
{
    /**
     * Validate if a face is active, priced, and free of overlapping reservations.
     */
    public function validate(InventoryFace $face, Carbon $startAt, Carbon $endAt): bool
    {
        // 1. Ensure face is active
        if (!$face->is_active) {
            return false;
        }

        $inventory = $face->inventory;
        if (!$inventory) {
            return false;
        }

        // 2. Ensure inventory is approved and ready for booking
        $readySpec = new InventoryReadyForBookingSpecification();
        if (!$readySpec->isSatisfiedBy($inventory)) {
            return false;
        }

        // 3. Ensure active pricing exists
        $priceSpec = new InventoryHasValidPricingSpecification();
        if (!$priceSpec->isSatisfiedBy($face)) {
            return false;
        }

        // 4. Ensure provider subscription is valid
        $subSpec = new InventoryHasActiveSubscriptionSpecification();
        if (!$subSpec->isSatisfiedBy($inventory)) {
            return false;
        }

        // 5. Check availability ledger overlaps (if there is an overlapping block or reservation)
        $overlapSpec = new AvailabilityOverlapSpecification($face->id, $startAt, $endAt);
        $overlaps = $face->availabilities->filter(function($avail) use ($overlapSpec) {
            return $overlapSpec->isSatisfiedBy($avail) && $avail->availability_status->value !== 'operational';
        });

        if ($overlaps->isNotEmpty()) {
            return false;
        }

        return true;
    }
}
