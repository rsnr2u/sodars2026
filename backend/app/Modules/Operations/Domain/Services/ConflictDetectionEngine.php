<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Services;

use App\Modules\Operations\Domain\Entities\Schedule;
use App\Modules\Operations\Domain\Entities\OperationalResource;
use App\Modules\Operations\Domain\Entities\ScheduleAssignment;
use App\Modules\Operations\Domain\Entities\ScheduleConflict;
use App\Modules\Operations\Domain\Enums\ConflictType;
use App\Modules\Operations\Domain\Events\ConflictDetected;
use App\Modules\Operations\Domain\Events\ConflictResolved;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ConflictDetectionEngine
{
    /**
     * Scan schedule for potential double-bookings or compliance violations.
     *
     * @return array<int, ScheduleConflict>
     */
    public function scanConflicts(Schedule $schedule): array
    {
        $conflicts = [];
        $start = Carbon::parse($schedule->start_time);
        $end = Carbon::parse($schedule->end_time);

        // Fetch assigned resources for this schedule
        $assignments = ScheduleAssignment::where('schedule_id', $schedule->id)
            ->whereNull('released_at')
            ->get();

        foreach ($assignments as $assignment) {
            $resource = $assignment->resource;
            if (!$resource) continue;

            // 1. Check double bookings
            $otherOverlap = ScheduleAssignment::where('resource_id', $resource->id)
                ->where('schedule_id', '!=', $schedule->id)
                ->whereNull('released_at')
                ->whereHas('schedule', function ($query) use ($start, $end) {
                    $query->where('start_time', '<', $end)
                          ->where('end_time', '>', $start);
                })
                ->first();

            if ($otherOverlap) {
                $conflict = ScheduleConflict::create([
                    'id' => (string) Str::uuid(),
                    'organization_id' => $schedule->organization_id,
                    'schedule_id' => $schedule->id,
                    'conflict_type' => ConflictType::DoubleBooking,
                    'severity' => 'critical',
                    'message' => "Resource [{$resource->display_name}] is double-booked on another schedule.",
                    'detected_at' => now(),
                ]);

                event(new ConflictDetected($schedule->id, 1, $conflict->toArray()));
                $conflicts[] = $conflict;
            }

            // 2. Check minimum rest period (11 hours between assignments)
            $previousAssignment = ScheduleAssignment::where('resource_id', $resource->id)
                ->where('schedule_id', '!=', $schedule->id)
                ->whereHas('schedule', function ($query) use ($start) {
                    $query->where('end_time', '<=', $start);
                })
                ->with('schedule')
                ->latest('assigned_at')
                ->first();

            if ($previousAssignment && $previousAssignment->schedule) {
                $lastEnd = Carbon::parse($previousAssignment->schedule->end_time);
                $restHours = $start->diffInHours($lastEnd);
                if ($restHours < 11) {
                    $conflict = ScheduleConflict::create([
                        'id' => (string) Str::uuid(),
                        'organization_id' => $schedule->organization_id,
                        'schedule_id' => $schedule->id,
                        'conflict_type' => ConflictType::RestPeriodViolation,
                        'severity' => 'warning',
                        'message' => "Rest period violation for [{$resource->display_name}]: only {$restHours} hours rest.",
                        'detected_at' => now(),
                    ]);

                    event(new ConflictDetected($schedule->id, 1, $conflict->toArray()));
                    $conflicts[] = $conflict;
                }
            }
        }

        return $conflicts;
    }

    /**
     * Mark detected conflicts as resolved.
     */
    public function resolveConflicts(Schedule $schedule, string $userId): void
    {
        $conflicts = ScheduleConflict::where('schedule_id', $schedule->id)
            ->whereNull('resolved_at')
            ->get();

        foreach ($conflicts as $conflict) {
            $conflict->update([
                'resolved_at' => now(),
                'resolved_by' => $userId,
            ]);

            event(new ConflictResolved($schedule->id, 1, $conflict->toArray()));
        }
    }
}
