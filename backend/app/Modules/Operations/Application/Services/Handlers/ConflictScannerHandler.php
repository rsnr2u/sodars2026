<?php

declare(strict_types=1);

namespace App\Modules\Operations\Application\Services\Handlers;

use App\Modules\Operations\Domain\Entities\Schedule;
use App\Modules\Operations\Domain\Services\ConflictDetectionEngine;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;

class ConflictScannerHandler
{
    public function __construct(protected ConflictDetectionEngine $engine) {}

    public function handle(ScheduledJob $job): void
    {
        $schedules = Schedule::whereIn('status', [
            \App\Modules\Operations\Domain\Enums\ScheduleStatus::Draft,
            \App\Modules\Operations\Domain\Enums\ScheduleStatus::Planned,
            \App\Modules\Operations\Domain\Enums\ScheduleStatus::Assigned,
        ])->get();

        foreach ($schedules as $schedule) {
            $this->engine->scanConflicts($schedule);
        }
    }
}
