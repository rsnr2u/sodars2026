<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Listeners;

use App\Modules\IoT\Domain\Events\DeviceTelemetryProcessed;
use App\Modules\IoT\Domain\Entities\DeviceAssignment;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Inventory\Domain\Entities\InventoryActivity;

class InventoryTelemetryListener
{
    /**
     * Handle telemetry events and log diagnostics for digital inventory faces.
     */
    public function handle(DeviceTelemetryProcessed $event): void
    {
        $deviceId = $event->aggregateId;
        $telemetryLog = $event->data;

        // Query active polymorphic assignment logs
        $assignment = DeviceAssignment::where('device_id', $deviceId)
            ->where('assignable_type', InventoryFace::class)
            ->whereNull('released_at')
            ->first();

        if ($assignment) {
            $face = InventoryFace::find($assignment->assignable_id);
            if ($face) {
                InventoryActivity::create([
                    'inventory_id' => $face->inventory_id,
                    'event_name' => 'device.telemetry',
                    'action' => 'diagnostics_received',
                    'new_values' => [
                        'face_id' => $face->id,
                        'device_id' => $deviceId,
                        'diagnostics' => $telemetryLog['diagnostics'] ?? [],
                    ],
                ]);
            }
        }
    }
}
