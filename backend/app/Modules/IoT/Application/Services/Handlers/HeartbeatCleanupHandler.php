<?php

declare(strict_types=1);

namespace App\Modules\IoT\Application\Services\Handlers;

use App\Platform\Scheduler\Application\Services\ScheduledJobHandler;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use App\Modules\IoT\Domain\Entities\DeviceHeartbeat;

class HeartbeatCleanupHandler implements ScheduledJobHandler
{
    public function handle(ScheduledJob $job): void
    {
        // Retain heartbeats for 30 days and clean up older logs
        DeviceHeartbeat::where('created_at', '<', now()->subDays(30))->delete();
    }
}
