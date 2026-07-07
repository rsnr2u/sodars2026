<?php

declare(strict_types=1);

namespace App\Modules\IoT\Application\Services\Handlers;

use App\Platform\Scheduler\Application\Services\ScheduledJobHandler;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use App\Modules\IoT\Domain\Services\DeviceLifecycleService;

class OfflineDetectionHandler implements ScheduledJobHandler
{
    public function handle(ScheduledJob $job): void
    {
        // Delegates offline scanning rules to the Lifecycle Service facade
        app(DeviceLifecycleService::class)->detectOfflineDevices();
    }
}
