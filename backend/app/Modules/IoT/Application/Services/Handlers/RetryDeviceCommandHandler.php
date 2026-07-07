<?php

declare(strict_types=1);

namespace App\Modules\IoT\Application\Services\Handlers;

use App\Platform\Scheduler\Application\Services\ScheduledJobHandler;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use App\Modules\IoT\Domain\Entities\DeviceCommand;
use App\Modules\IoT\Domain\Services\DeviceLifecycleService;

class RetryDeviceCommandHandler implements ScheduledJobHandler
{
    public function handle(ScheduledJob $job): void
    {
        $commandId = $job->payload['device_command_id'] ?? null;
        if ($commandId) {
            $command = DeviceCommand::find($commandId);
            if ($command && $command->status->value === 'Queued') {
                app(DeviceLifecycleService::class)->dispatchCommand($command);
            }
        }
    }
}
