<?php

declare(strict_types=1);

namespace App\Modules\Operations\Application\Services\Handlers;

use App\Modules\Operations\Domain\Entities\ScheduleCheckpoint;
use App\Modules\Operations\Domain\Entities\Schedule;
use App\Modules\Operations\Domain\Enums\ScheduleStatus;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use Illuminate\Support\Facades\Log;

class SLAEscalationHandler
{
    public function handle(ScheduledJob $job): void
    {
        // Check active dispatches where a pending checkpoint is delayed past its schedule end time
        $overdueCheckpoints = ScheduleCheckpoint::where('status', 'pending')
            ->whereHas('schedule', function ($query) {
                $query->where('status', ScheduleStatus::InProgress)
                      ->where('end_time', '<', now());
            })
            ->with('schedule')
            ->get();

        foreach ($overdueCheckpoints as $checkpoint) {
            if ($checkpoint->schedule) {
                Log::warning("SLA ESCALATION: Schedule [{$checkpoint->schedule->schedule_number}] has pending checkpoint [{$checkpoint->name}] past planned end time!");
            }
        }
    }
}
