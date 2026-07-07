<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Services;

use App\Modules\Operations\Domain\Entities\OperationalResource;
use App\Modules\Operations\Domain\Entities\ScheduleAssignment;
use App\Modules\Operations\Domain\Entities\ResourceAvailabilityProjection;
use Carbon\Carbon;

class AvailabilityEngine
{
    /**
     * Check if a resource is available within a target date range slot.
     */
    public function checkAvailability(OperationalResource $resource, Carbon $start, Carbon $end): bool
    {
        // 1. Query read-only availability projection blocks
        $projection = ResourceAvailabilityProjection::where('resource_id', $resource->id)->first();
        if ($projection && !empty($projection->blocked_time_slots)) {
            foreach ($projection->blocked_time_slots as $slot) {
                $blockStart = Carbon::parse($slot['start']);
                $blockEnd = Carbon::parse($slot['end']);

                // Overlap check
                if ($start->lessThan($blockEnd) && $end->greaterThan($blockStart)) {
                    return false;
                }
            }
        }

        // 2. Double check temporal assignment history logs (fail safe)
        $conflictingAssignmentsCount = ScheduleAssignment::where('resource_id', $resource->id)
            ->whereNull('released_at')
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    $q->where('assigned_at', '<', $end)
                      ->where('assigned_at', '>=', $start);
                })->orWhere(function ($q) use ($start) {
                    $q->where('assigned_at', '<=', $start)
                      ->whereNull('released_at');
                });
            })
            ->count();

        return $conflictingAssignmentsCount === 0;
    }
}
