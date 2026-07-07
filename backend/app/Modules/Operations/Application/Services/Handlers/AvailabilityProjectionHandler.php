<?php

declare(strict_types=1);

namespace App\Modules\Operations\Application\Services\Handlers;

use App\Modules\Operations\Domain\Entities\OperationalResource;
use App\Modules\Operations\Domain\Entities\ScheduleAssignment;
use App\Modules\Operations\Domain\Entities\ResourceAvailabilityProjection;
use App\Modules\Operations\Domain\Enums\ResourceState;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use Illuminate\Support\Str;

class AvailabilityProjectionHandler
{
    public function handle(ScheduledJob $job): void
    {
        $resources = OperationalResource::all();

        foreach ($resources as $resource) {
            $activeAssignments = ScheduleAssignment::where('resource_id', $resource->id)
                ->whereNull('released_at')
                ->with('schedule')
                ->get();

            $slots = [];
            $currentState = ResourceState::Available;

            foreach ($activeAssignments as $assignment) {
                if ($assignment->schedule) {
                    $slots[] = [
                        'start' => $assignment->schedule->start_time->toDateTimeString(),
                        'end' => $assignment->schedule->end_time->toDateTimeString(),
                    ];
                    $currentState = ResourceState::Assigned;
                }
            }

            $projection = ResourceAvailabilityProjection::firstOrCreate(
                ['resource_id' => $resource->id],
                ['id' => (string) Str::uuid(), 'organization_id' => $resource->organization_id, 'last_updated_at' => now()]
            );

            $projection->update([
                'current_state' => $currentState,
                'blocked_time_slots' => $slots,
                'last_updated_at' => now(),
            ]);
        }
    }
}
