<?php

declare(strict_types=1);

namespace App\Modules\Operations\Application\Services\Handlers;

use App\Modules\Operations\Domain\Entities\Schedule;
use App\Modules\Operations\Domain\Enums\ScheduleStatus;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use Illuminate\Support\Facades\Log;

class DispatchReminderHandler
{
    public function handle(ScheduledJob $job): void
    {
        // Fetch schedules starting in next 30 minutes that aren't dispatched yet
        $schedules = Schedule::where('status', ScheduleStatus::Approved)
            ->where('start_time', '<=', now()->addMinutes(30))
            ->where('start_time', '>', now())
            ->get();

        foreach ($schedules as $schedule) {
            Log::info("Dispatch reminder triggered for Schedule [{$schedule->schedule_number}] starts at [{$schedule->start_time}].");
        }
    }
}
