<?php

declare(strict_types=1);

namespace App\Modules\Transport\Application\Listeners;

use App\Modules\IoT\Domain\Events\DeviceTelemetryProcessed;
use App\Modules\IoT\Domain\Entities\DeviceAssignment;
use App\Modules\Transport\Application\Services\TransportService;
use App\Modules\Transport\Domain\Entities\Vehicle;

class TransportTelemetryListener
{
    /**
     * Handle telemetry events and log GPS coordinates for assigned vehicles.
     */
    public function handle(DeviceTelemetryProcessed $event): void
    {
        $deviceId = $event->aggregateId;
        $telemetryLog = $event->data;

        // Query active polymorphic assignment logs
        $assignment = DeviceAssignment::where('device_id', $deviceId)
            ->where('assignable_type', Vehicle::class)
            ->whereNull('released_at')
            ->first();

        if ($assignment) {
            $transport = app(TransportService::class);
            $transport->logGPS($assignment->assignable_id, [
                'latitude' => $telemetryLog['latitude'] ?? 0.0,
                'longitude' => $telemetryLog['longitude'] ?? 0.0,
                'speed_kmh' => $telemetryLog['speed_kph'] ?? 0.0,
                'heading' => $telemetryLog['heading_degrees'] ?? null,
            ]);
        }
    }
}
