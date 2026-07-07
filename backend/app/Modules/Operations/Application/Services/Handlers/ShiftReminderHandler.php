<?php

declare(strict_types=1);

namespace App\Modules\Operations\Application\Services\Handlers;

use App\Modules\Operations\Domain\Entities\Shift;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use Illuminate\Support\Facades\Log;

class ShiftReminderHandler
{
    public function handle(ScheduledJob $job): void
    {
        $activeShiftsCount = Shift::where('status', 'active')->count();
        Log::info("Active shifts checklist count: {$activeShiftsCount}.");
    }
}
