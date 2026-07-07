<?php

declare(strict_types=1);

namespace App\Modules\IoT\Application\Services\Handlers;

use App\Platform\Scheduler\Application\Services\ScheduledJobHandler;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use App\Modules\IoT\Domain\Entities\Device;
use App\Modules\IoT\Domain\Entities\FirmwarePackage;
use App\Modules\IoT\Domain\Services\DeviceLifecycleService;

class FirmwareDeploymentHandler implements ScheduledJobHandler
{
    public function handle(ScheduledJob $job): void
    {
        $payload = $job->payload;
        $deviceId = $payload['device_id'] ?? null;
        $packageId = $payload['firmware_package_id'] ?? null;

        if ($deviceId && $packageId) {
            $device = Device::find($deviceId);
            $package = FirmwarePackage::find($packageId);
            if ($device && $package) {
                app(DeviceLifecycleService::class)->installFirmware($device, $package);
            }
        }
    }
}
