<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Managers;

use App\Modules\Operations\Domain\Entities\Schedule;
use App\Modules\Operations\Domain\Entities\ScheduleExecution;
use App\Modules\Operations\Domain\Entities\ScheduleSnapshot;
use App\Modules\Operations\Domain\Entities\ScheduleTimeline;
use App\Modules\Operations\Domain\Enums\ScheduleStatus;
use App\Platform\Identifiers\ScheduleNumberGenerator;
use App\Modules\Operations\Domain\Events\ScheduleCreated;
use App\Modules\Operations\Domain\Events\ScheduleValidated;
use App\Modules\Operations\Domain\Events\ScheduleOptimized;
use App\Modules\Operations\Domain\Events\ScheduleAssigned;
use App\Modules\Operations\Domain\Events\ScheduleApproved;
use App\Modules\Operations\Domain\Events\ScheduleDispatched;
use App\Modules\Operations\Domain\Events\ScheduleStarted;
use App\Modules\Operations\Domain\Events\ScheduleCompleted;
use App\Modules\Operations\Domain\Events\ScheduleCancelled;
use App\Modules\Operations\Domain\Events\ScheduleDelayed;
use App\Modules\Operations\Domain\Events\ScheduleSuspended;
use App\Modules\Operations\Domain\Events\ScheduleFailed;
use Illuminate\Support\Str;

class ScheduleLifecycleManager
{
    public function __construct(protected ScheduleNumberGenerator $numberGenerator) {}

    public function create(array $data): Schedule
    {
        $data['schedule_number'] = $this->numberGenerator->generate();
        $data['status'] = ScheduleStatus::Draft;

        $schedule = Schedule::create($data);

        // Create the execution tracking child record
        ScheduleExecution::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $schedule->organization_id,
            'schedule_id' => $schedule->id,
            'execution_status' => ScheduleStatus::Draft,
        ]);

        $this->recordTimeline($schedule, 'ExecutionCreated', 'Planned schedule initialized.');

        event(new ScheduleCreated($schedule->id, 1, $schedule->toArray()));

        return $schedule;
    }

    public function transition(Schedule $schedule, ScheduleStatus $status, ?string $reason = null): void
    {
        $oldStatus = $schedule->status;
        $schedule->update(['status' => $status]);

        // Replicate status to execution child record if matching runtime states
        $execution = $schedule->execution;
        if ($execution) {
            $execution->update(['execution_status' => $status]);
        }

        // Record structured timeline entries
        $eventName = $this->getTimelineEventName($status);
        $this->recordTimeline($schedule, $eventName, $reason ?? "Transitioned schedule to {$status->value}.");

        // Trigger snapshotting on approved, optimized, or dispatched transitions
        if (in_array($status, [ScheduleStatus::Approved, ScheduleStatus::Optimized, ScheduleStatus::Dispatched], true)) {
            $this->captureSnapshot($schedule, $status->name);
        }

        // Emit corresponding events
        $eventClass = $this->getEventClassForStatus($status);
        if ($eventClass) {
            event(new $eventClass($schedule->id, 1, $schedule->toArray()));
        }
    }

    protected function captureSnapshot(Schedule $schedule, string $triggerState): void
    {
        ScheduleSnapshot::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $schedule->organization_id,
            'schedule_id' => $schedule->id,
            'trigger_state' => $triggerState,
            'snapshot_data' => $schedule->load(['assignments', 'shift', 'calendar'])->toArray(),
            'captured_at' => now(),
        ]);
    }

    protected function recordTimeline(Schedule $schedule, string $eventName, string $description): void
    {
        ScheduleTimeline::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $schedule->organization_id,
            'schedule_id' => $schedule->id,
            'event_name' => $eventName,
            'description' => $description,
            'occurred_at' => now(),
        ]);
    }

    protected function getTimelineEventName(ScheduleStatus $status): string
    {
        return match ($status) {
            ScheduleStatus::Draft => 'ExecutionCreated',
            ScheduleStatus::Validated => 'ScheduleValidated',
            ScheduleStatus::Optimized => 'ScheduleOptimized',
            ScheduleStatus::Assigned => 'ScheduleAssigned',
            ScheduleStatus::Approved => 'ScheduleApproved',
            ScheduleStatus::Dispatched => 'ScheduleDispatched',
            ScheduleStatus::InProgress => 'ExecutionStarted',
            ScheduleStatus::Completed => 'ExecutionCompleted',
            ScheduleStatus::Cancelled => 'ExecutionCancelled',
            ScheduleStatus::Delayed => 'ETALateDetected',
            ScheduleStatus::Suspended => 'ExecutionPaused',
            ScheduleStatus::Failed => 'ExecutionFailed',
            default => 'ScheduleUpdated',
        };
    }

    protected function getEventClassForStatus(ScheduleStatus $status): ?string
    {
        return match ($status) {
            ScheduleStatus::Draft => ScheduleCreated::class,
            ScheduleStatus::Validated => ScheduleValidated::class,
            ScheduleStatus::Optimized => ScheduleOptimized::class,
            ScheduleStatus::Assigned => ScheduleAssigned::class,
            ScheduleStatus::Approved => ScheduleApproved::class,
            ScheduleStatus::Dispatched => ScheduleDispatched::class,
            ScheduleStatus::InProgress => ScheduleStarted::class,
            ScheduleStatus::Completed => ScheduleCompleted::class,
            ScheduleStatus::Cancelled => ScheduleCancelled::class,
            ScheduleStatus::Delayed => ScheduleDelayed::class,
            ScheduleStatus::Suspended => ScheduleSuspended::class,
            ScheduleStatus::Failed => ScheduleFailed::class,
            default => null,
        };
    }
}
