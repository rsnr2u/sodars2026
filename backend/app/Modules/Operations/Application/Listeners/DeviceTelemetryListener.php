<?php

declare(strict_types=1);

namespace App\Modules\Operations\Application\Listeners;

use App\Modules\IoT\Domain\Events\DeviceTelemetryProcessed;
use App\Modules\IoT\Domain\Entities\DeviceAssignment;
use App\Modules\Operations\Domain\Entities\OperationalResource;
use App\Modules\Operations\Domain\Entities\ScheduleAssignment;
use App\Modules\Operations\Domain\Entities\Schedule;
use App\Modules\Operations\Domain\Enums\ScheduleStatus;
use App\Modules\Operations\Domain\Services\OperationsLifecycleService;
use App\Modules\Transport\Domain\Entities\Vehicle;

class DeviceTelemetryListener
{
    /**
     * Listen to telemetry check-ins and update active scheduling ETA progress metrics.
     */
    public function handle(DeviceTelemetryProcessed $event): void
    {
        $deviceId = $event->aggregateId;
        $telemetryLog = $event->data;

        $lat = isset($telemetryLog['latitude']) ? (float) $telemetryLog['latitude'] : null;
        $lon = isset($telemetryLog['longitude']) ? (float) $telemetryLog['longitude'] : null;
        $speed = isset($telemetryLog['speed_kph']) ? (float) $telemetryLog['speed_kph'] : 0.0;

        if ($lat === null || $lon === null) {
            return;
        }

        // 1. Resolve vehicle assigned to this device
        $deviceAssignment = DeviceAssignment::where('device_id', $deviceId)
            ->where('assignable_type', Vehicle::class)
            ->whereNull('released_at')
            ->first();

        if (!$deviceAssignment) {
            return;
        }

        $vehicleId = $deviceAssignment->assignable_id;

        // 2. Resolve operational resource wrapper for this vehicle
        $resource = OperationalResource::where('resource_type', 'vehicle')
            ->where('external_id', $vehicleId)
            ->first();

        if (!$resource) {
            return;
        }

        // 3. Resolve active dispatch schedule assigned to this resource
        $scheduleAssignment = ScheduleAssignment::where('resource_id', $resource->id)
            ->whereNull('released_at')
            ->whereHas('schedule', function ($query) {
                $query->whereIn('status', [ScheduleStatus::InProgress, ScheduleStatus::Dispatched]);
            })
            ->first();

        if ($scheduleAssignment && $scheduleAssignment->schedule) {
            app(OperationsLifecycleService::class)->recordTelemetryUpdate(
                $scheduleAssignment->schedule,
                $lat,
                $lon,
                $speed
            );
        }
    }
}
