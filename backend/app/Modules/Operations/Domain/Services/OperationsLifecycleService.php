<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Services;

use App\Modules\Operations\Domain\Entities\Schedule;
use App\Modules\Operations\Domain\Entities\ScheduleAssignment;
use App\Modules\Operations\Domain\Entities\ScheduleExecution;
use App\Modules\Operations\Domain\Entities\OperationalResource;
use App\Modules\Operations\Domain\Entities\RecurrenceRule;
use App\Modules\Operations\Domain\Entities\ScheduleCheckpoint;
use App\Modules\Operations\Domain\Entities\ScheduleTimeline;
use App\Modules\Operations\Domain\Entities\DispatchProgressProjection;
use App\Modules\Operations\Domain\Enums\ScheduleStatus;
use App\Modules\Operations\Domain\Enums\ResourceState;
use App\Modules\Operations\Domain\Managers\ScheduleLifecycleManager;
use App\Modules\Operations\Domain\Managers\ShiftLifecycleManager;
use App\Modules\Operations\Domain\Managers\CalendarLifecycleManager;
use App\Modules\Operations\Domain\Managers\ResourceLifecycleManager;
use App\Modules\Operations\Domain\ValueObjects\RecurrencePattern;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OperationsLifecycleService
{
    public function __construct(
        protected ScheduleLifecycleManager $scheduleManager,
        protected ShiftLifecycleManager $shiftManager,
        protected CalendarLifecycleManager $calendarManager,
        protected ResourceLifecycleManager $resourceManager,
        protected ConflictDetectionEngine $conflictEngine,
        protected ETAEngine $etaEngine,
        protected RecurrenceEngine $recurrenceEngine
    ) {}

    public function createSchedule(array $data): Schedule
    {
        return $this->scheduleManager->create($data);
    }

    public function transitionSchedule(Schedule $schedule, ScheduleStatus $status, ?string $reason = null): void
    {
        $this->scheduleManager->transition($schedule, $status, $reason);
    }

    public function assignResource(Schedule $schedule, OperationalResource $resource): void
    {
        // Create Assignment
        ScheduleAssignment::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $schedule->organization_id,
            'schedule_id' => $schedule->id,
            'resource_id' => $resource->id,
            'assigned_at' => now(),
        ]);

        // Transition resource state in history logs
        $this->resourceManager->recordStateChange($resource, ResourceState::Assigned, "Assigned to schedule [{$schedule->schedule_number}].");

        // Scan for conflicts
        $this->conflictEngine->scanConflicts($schedule);
    }

    public function releaseResource(Schedule $schedule, OperationalResource $resource, string $reason): void
    {
        ScheduleAssignment::where('schedule_id', $schedule->id)
            ->where('resource_id', $resource->id)
            ->whereNull('released_at')
            ->update([
                'released_at' => now(),
                'released_reason' => $reason,
            ]);

        // Revert resource state in history logs
        $this->resourceManager->recordStateChange($resource, ResourceState::Available, "Released from schedule [{$schedule->schedule_number}]. Reason: {$reason}.");
    }

    public function recordTelemetryUpdate(Schedule $schedule, float $lat, float $lon, float $speedKmh): void
    {
        $execution = $schedule->execution;
        if (!$execution) return;

        // Fetch active checkpoint
        $nextCheckpoint = ScheduleCheckpoint::where('schedule_id', $schedule->id)
            ->where('status', 'pending')
            ->orderBy('sequence', 'asc')
            ->first();

        if ($nextCheckpoint && $nextCheckpoint->latitude && $nextCheckpoint->longitude) {
            // Compute ETA Estimate via ETAEngine
            $estimate = $this->etaEngine->calculateETA(
                $lat,
                $lon,
                $nextCheckpoint->latitude,
                $nextCheckpoint->longitude,
                $speedKmh
            );

            $execution->update([
                'current_eta' => Carbon::parse($estimate->estimatedArrivalTime),
                'actual_distance_meters' => ($execution->actual_distance_meters ?? 0.0) + $estimate->remainingDistanceMeters,
            ]);

            // Create structured timeline events
            ScheduleTimeline::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $schedule->organization_id,
                'schedule_id' => $schedule->id,
                'event_name' => 'ETAUpdated',
                'description' => "ETA estimate updated to {$estimate->estimatedArrivalTime}. Remaining distance: {$estimate->remainingDistanceMeters}m.",
                'payload' => $estimate->toArray(),
                'occurred_at' => now(),
            ]);
        }
    }

    public function resolveScheduleConflicts(Schedule $schedule, string $userId): void
    {
        $this->conflictEngine->resolveConflicts($schedule, $userId);
    }

    public function generateScheduleRecurrences(Schedule $schedule, RecurrencePattern $pattern, array $holidays = []): array
    {
        // 1. Create Recurrence Rule
        RecurrenceRule::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $schedule->organization_id,
            'schedule_id' => $schedule->id,
            'frequency' => $pattern->frequency,
            'interval' => $pattern->interval,
            'by_days' => $pattern->byDays,
            'exception_dates' => $pattern->exceptionDates,
            'ends_at' => $pattern->endsAt ? Carbon::parse($pattern->endsAt) : null,
        ]);

        // 2. Generate future occurrence date slots
        $slots = $this->recurrenceEngine->generateOccurrences(
            Carbon::parse($schedule->start_time),
            Carbon::parse($schedule->end_time),
            $pattern,
            $holidays
        );

        $created = [];
        foreach ($slots as $slot) {
            $created[] = $this->createSchedule([
                'organization_id' => $schedule->organization_id,
                'calendar_id' => $schedule->calendar_id,
                'shift_id' => $schedule->shift_id,
                'name' => "{$schedule->name} (Occurrence)",
                'schedule_type' => $schedule->schedule_type,
                'start_time' => $slot['start']->toDateTimeString(),
                'end_time' => $slot['end']->toDateTimeString(),
            ]);
        }

        return $created;
    }
}
