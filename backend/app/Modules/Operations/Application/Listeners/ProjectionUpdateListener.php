<?php

declare(strict_types=1);

namespace App\Modules\Operations\Application\Listeners;

use App\Modules\Operations\Domain\Events\AbstractSchedulingEvent;
use App\Modules\Operations\Domain\Entities\Schedule;
use App\Modules\Operations\Domain\Entities\ScheduleAssignment;
use App\Modules\Operations\Domain\Entities\ResourceAvailabilityProjection;
use App\Modules\Operations\Domain\Entities\ResourceWorkloadProjection;
use App\Modules\Operations\Domain\Entities\DispatchProgressProjection;
use App\Modules\Operations\Domain\Enums\ResourceState;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProjectionUpdateListener
{
    public function handle(AbstractSchedulingEvent $event): void
    {
        $scheduleId = $event->aggregateId;
        $schedule = Schedule::find($scheduleId);
        if (!$schedule) return;

        // Rebuild Workload and Availability projections for all resources assigned to this schedule
        $assignments = ScheduleAssignment::where('schedule_id', $scheduleId)->get();

        foreach ($assignments as $assignment) {
            $resourceId = $assignment->resource_id;

            $this->rebuildAvailability($schedule->organization_id, $resourceId);
            $this->rebuildWorkload($schedule->organization_id, $resourceId);
        }

        // Rebuild Dispatch Progress Projection
        $this->rebuildDispatchProgress($schedule);
    }

    protected function rebuildAvailability(string $orgId, string $resourceId): void
    {
        $projection = ResourceAvailabilityProjection::firstOrCreate(
            ['resource_id' => $resourceId],
            ['id' => (string) Str::uuid(), 'organization_id' => $orgId, 'last_updated_at' => now()]
        );

        // Fetch active assignments to build blocked time slots
        $activeAssignments = ScheduleAssignment::where('resource_id', $resourceId)
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

        $projection->update([
            'current_state' => $currentState,
            'blocked_time_slots' => $slots,
            'last_updated_at' => now(),
        ]);
    }

    protected function rebuildWorkload(string $orgId, string $resourceId): void
    {
        $projection = ResourceWorkloadProjection::firstOrCreate(
            ['resource_id' => $resourceId],
            ['id' => (string) Str::uuid(), 'organization_id' => $orgId]
        );

        $activeAssignments = ScheduleAssignment::where('resource_id', $resourceId)
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

        // Compute utilization score: (totalSeconds / (8 hours * 3600 seconds)) * 100
        $maxDailyWorkSeconds = 8 * 3600;
        $score = min(100, (int) (($totalSeconds / $maxDailyWorkSeconds) * 100));

        $projection->update([
            'assigned_schedules_count' => $count,
            'total_allocated_seconds' => $totalSeconds,
            'utilization_score' => $score,
        ]);
    }

    protected function rebuildDispatchProgress(Schedule $schedule): void
    {
        $execution = $schedule->execution;
        if (!$execution) return;

        $checkpoints = $schedule->checkpoints()->orderBy('sequence', 'asc')->get();
        $total = $checkpoints->count();
        if ($total === 0) return;

        $completed = $checkpoints->where('status', 'reached')->count();
        $percent = (int) (($completed / $total) * 100);

        $projection = DispatchProgressProjection::firstOrCreate(
            ['schedule_id' => $schedule->id],
            [
                'id' => (string) Str::uuid(),
                'organization_id' => $schedule->organization_id,
                'execution_id' => $execution->id,
            ]
        );

        $projection->update([
            'completed_checkpoints_count' => $completed,
            'total_checkpoints_count' => $total,
            'completion_percentage' => $percent,
            'eta_estimate' => $execution->current_eta,
        ]);
    }
}
