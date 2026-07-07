<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Services;

use App\Modules\Operations\Domain\Entities\OperationalResource;
use App\Modules\Operations\Domain\Entities\ResourceWorkloadProjection;
use Carbon\Carbon;

class CapacityEngine
{
    /**
     * Verify if resource capacity threshold is not exceeded.
     */
    public function verifyCapacity(OperationalResource $resource, Carbon $start, Carbon $end): bool
    {
        $projection = ResourceWorkloadProjection::where('resource_id', $resource->id)->first();
        if ($projection) {
            // If utilization score is above 90%, mark capacity as fully loaded
            if ($projection->utilization_score >= 90) {
                return false;
            }

            // If active count exceeds threshold (e.g., 5 daily shifts max)
            if ($projection->assigned_schedules_count >= 5) {
                return false;
            }
        }

        return true;
    }
}
