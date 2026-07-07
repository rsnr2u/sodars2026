<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Services;

use App\Modules\IoT\Domain\Entities\Device;
use App\Modules\IoT\Domain\Entities\DeviceTelemetryLog;
use App\Modules\IoT\Domain\Entities\DeviceHeartbeat;
use App\Modules\IoT\Domain\Managers\TelemetryLifecycleManager;

class TelemetryProcessor
{
    public function __construct(
        protected TelemetryLifecycleManager $telemetryManager
    ) {}

    /**
     * Process incoming telemetry, updates health snapshots and dispatches events.
     */
    public function process(Device $device, array $data): DeviceTelemetryLog
    {
        // Log immutable telemetry record and dispatches DeviceTelemetryProcessed event
        return $this->telemetryManager->recordTelemetry($device, $data);
    }

    /**
     * Process heartbeat check-ins.
     */
    public function processHeartbeat(Device $device, array $data): DeviceHeartbeat
    {
        return $this->telemetryManager->recordHeartbeat($device, $data);
    }
}
