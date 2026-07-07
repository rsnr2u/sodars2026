<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Managers;

use App\Modules\IoT\Domain\Entities\Device;
use App\Modules\IoT\Domain\Entities\DeviceAssignment;
use App\Modules\IoT\Domain\Entities\DeviceHealthSnapshot;
use App\Modules\IoT\Domain\Enums\DeviceStatus;
use App\Modules\IoT\Domain\Enums\DeviceType;
use App\Platform\Identifiers\DeviceNumberGenerator;
use App\Modules\IoT\Domain\Events\DeviceRegistered;
use App\Modules\IoT\Domain\Events\DeviceActivated;
use App\Modules\IoT\Domain\Events\DeviceSuspended;
use App\Modules\IoT\Domain\Events\DeviceAssigned;
use App\Modules\IoT\Domain\Events\DeviceReleased;
use Illuminate\Support\Str;

class DeviceLifecycleManager
{
    public function __construct(protected DeviceNumberGenerator $numberGenerator) {}

    /**
     * Register a new hardware device.
     */
    public function register(array $data): Device
    {
        $deviceNumber = $this->numberGenerator->generate();

        $device = Device::create(array_merge($data, [
            'id' => (string) Str::uuid(),
            'device_number' => $deviceNumber,
            'status' => DeviceStatus::Active, // Default Active
            'device_secret' => $data['device_secret'] ?? Str::random(32),
        ]));

        // Create health snapshot
        DeviceHealthSnapshot::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $device->organization_id,
            'device_id' => $device->id,
            'overall_health_score' => 100,
            'last_seen_at' => now(),
        ]);

        event(new DeviceRegistered($device->id, 1, $device->toArray()));

        return $device;
    }

    /**
     * Activate device.
     */
    public function activate(Device $device): void
    {
        $device->update(['status' => DeviceStatus::Active]);
        event(new DeviceActivated($device->id, 1, $device->toArray()));
    }

    /**
     * Suspend device.
     */
    public function suspend(Device $device): void
    {
        $device->update(['status' => DeviceStatus::Offline]);
        event(new DeviceSuspended($device->id, 1, $device->toArray()));
    }

    /**
     * Assign a device polymorphically to an asset.
     */
    public function assign(Device $device, string $assignableType, string $assignableId, string $assignedBy): void
    {
        // 1. Release active assignments first
        $this->releaseActiveAssignments($device, 'Reassignment.', $assignedBy);

        // 2. Create temporal assignment log
        $assignment = DeviceAssignment::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $device->organization_id,
            'device_id' => $device->id,
            'assignable_type' => $assignableType,
            'assignable_id' => $assignableId,
            'assigned_at' => now(),
            'assigned_by' => $assignedBy,
        ]);

        event(new DeviceAssigned($device->id, 1, $assignment->toArray()));
    }

    /**
     * Release active device assignments.
     */
    public function release(Device $device, string $reason, string $releasedBy): void
    {
        $this->releaseActiveAssignments($device, $reason, $releasedBy);
        event(new DeviceReleased($device->id, 1, ['released_by' => $releasedBy, 'reason' => $reason]));
    }

    /**
     * Release all active assignments.
     */
    protected function releaseActiveAssignments(Device $device, string $reason, string $releasedBy): void
    {
        DeviceAssignment::where('device_id', $device->id)
            ->whereNull('released_at')
            ->update([
                'released_at' => now(),
                'released_reason' => $reason,
                'released_by' => $releasedBy,
            ]);
    }
}
