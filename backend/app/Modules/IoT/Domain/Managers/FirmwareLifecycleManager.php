<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Managers;

use App\Modules\IoT\Domain\Entities\Device;
use App\Modules\IoT\Domain\Entities\FirmwarePackage;
use App\Modules\IoT\Domain\Entities\DeviceFirmwareInstallation;
use App\Modules\IoT\Domain\Enums\FirmwareInstallationStatus;
use App\Modules\IoT\Domain\Events\FirmwarePublished;
use App\Modules\IoT\Domain\Events\FirmwareInstalled;
use App\Modules\IoT\Domain\Events\FirmwareRollback;
use Illuminate\Support\Str;

class FirmwareLifecycleManager
{
    /**
     * Publish a new firmware package.
     */
    public function publishPackage(array $data): FirmwarePackage
    {
        $package = FirmwarePackage::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $data['organization_id'],
            'version' => $data['version'],
            'sha256' => $data['sha256'],
            'size_bytes' => (int) $data['size_bytes'],
            'signature' => $data['signature'],
            'signature_algorithm' => $data['signature_algorithm'] ?? 'RSA-SHA256',
            'download_url' => $data['download_url'],
            'min_supported_version' => $data['min_supported_version'] ?? null,
            'max_supported_version' => $data['max_supported_version'] ?? null,
            'compatible_device_types' => $data['compatible_device_types'] ?? [],
            'published_at' => now(),
        ]);

        event(new FirmwarePublished($package->id, 1, $package->toArray()));

        return $package;
    }

    /**
     * Trigger firmware installation rollout on a device.
     */
    public function install(Device $device, FirmwarePackage $package): DeviceFirmwareInstallation
    {
        $installation = DeviceFirmwareInstallation::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $device->organization_id,
            'device_id' => $device->id,
            'firmware_package_id' => $package->id,
            'status' => FirmwareInstallationStatus::Scheduled,
            'started_at' => now(),
        ]);

        // Queue upgrade command to the device
        app(CommandLifecycleManager::class)->queueCommand($device, 'UpgradeFirmware', [
            'version' => $package->version,
            'download_url' => $package->download_url,
            'sha256' => $package->sha256,
        ]);

        event(new FirmwareInstalled($device->id, 1, $package->toArray()));

        return $installation;
    }

    /**
     * Trigger rollback of a failed installation.
     */
    public function rollback(Device $device, DeviceFirmwareInstallation $installation): void
    {
        $installation->update([
            'status' => FirmwareInstallationStatus::Rollback,
            'completed_at' => now(),
        ]);

        event(new FirmwareRollback($device->id, 1, $installation->toArray()));
    }
}
