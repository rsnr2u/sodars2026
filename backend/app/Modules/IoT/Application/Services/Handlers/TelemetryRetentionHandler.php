<?php

declare(strict_types=1);

namespace App\Modules\IoT\Application\Services\Handlers;

use App\Platform\Scheduler\Application\Services\ScheduledJobHandler;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use App\Modules\IoT\Domain\Entities\DeviceTelemetryLog;

class TelemetryRetentionHandler implements ScheduledJobHandler
{
    public function handle(ScheduledJob $job): void
    {
        // Retain telemetry logs for 90 days and clean up older logs
        DeviceTelemetryLog::where('logged_at', '<', now()->subDays(90))->delete();
    }
}
