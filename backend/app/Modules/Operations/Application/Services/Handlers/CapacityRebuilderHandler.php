<?php

declare(strict_types=1);

namespace App\Modules\Operations\Application\Services\Handlers;

use App\Modules\Operations\Domain\Entities\OperationalResource;
use App\Modules\Operations\Domain\Entities\ScheduleAssignment;
use App\Modules\Operations\Domain\Entities\ResourceWorkloadProjection;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CapacityRebuilderHandler
{
    public function handle(ScheduledJob $job): void
    {
        $resources = OperationalResource::all();

        foreach ($resources as $resource) {
            $activeAssignments = ScheduleAssignment::where('resource_id', $resource->id)
                ->whereNull('released_at')
                ->with('schedule')
                ->get();

            $count = $activeAssignments->count();
            $totalSeconds = 0;

            foreach ($activeAssignments as $assignment) {
                if ($assignment->schedule) {
                    $start = Carbon::parse($assignment->schedule->start_time);
                    $end = Carbon::parse($assignment->schedule->end_time);
                    $totalSeconds += $end->diffInSeconds($start);
                }
            }

            $maxDailyWorkSeconds = 8 * 3600;
            $score = min(100, (int) (($totalSeconds / $maxDailyWorkSeconds) * 100));

            $projection = ResourceWorkloadProjection::firstOrCreate(
                ['resource_id' => $resource->id],
                ['id' => (string) Str::uuid(), 'organization_id' => $resource->organization_id]
            );

            $projection->update([
                'assigned_schedules_count' => $count,
                'total_allocated_seconds' => $totalSeconds,
                'utilization_score' => $score,
            ]);
        }
    }
}
