<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Services;

use App\Modules\IoT\Domain\Entities\Device;
use App\Modules\IoT\Domain\Entities\DeviceHeartbeat;
use App\Modules\IoT\Domain\Entities\DeviceTelemetryLog;
use App\Modules\IoT\Domain\Entities\DeviceCommand;
use App\Modules\IoT\Domain\Entities\FirmwarePackage;
use App\Modules\IoT\Domain\Entities\DeviceFirmwareInstallation;
use App\Modules\IoT\Domain\Managers\DeviceLifecycleManager;
use App\Modules\IoT\Domain\Managers\TelemetryLifecycleManager;
use App\Modules\IoT\Domain\Managers\FirmwareLifecycleManager;
use App\Modules\IoT\Domain\Managers\CommandLifecycleManager;

class DeviceLifecycleService
{
    public function __construct(
        protected DeviceLifecycleManager $deviceManager,
        protected TelemetryLifecycleManager $telemetryManager,
        protected FirmwareLifecycleManager $firmwareManager,
        protected CommandLifecycleManager $commandManager
    ) {}

    public function registerDevice(array $data): Device
    {
        return $this->deviceManager->register($data);
    }

    public function activateDevice(Device $device): void
    {
        $this->deviceManager->activate($device);
    }

    public function suspendDevice(Device $device): void
    {
        $this->deviceManager->suspend($device);
    }

    public function assignDevice(Device $device, string $assignableType, string $assignableId, string $assignedBy): void
    {
        $this->deviceManager->assign($device, $assignableType, $assignableId, $assignedBy);
    }

    public function releaseDevice(Device $device, string $reason, string $releasedBy): void
    {
        $this->deviceManager->release($device, $reason, $releasedBy);
    }

    public function recordHeartbeat(Device $device, array $data): DeviceHeartbeat
    {
        return $this->telemetryManager->recordHeartbeat($device, $data);
    }

    public function recordTelemetry(Device $device, array $data): DeviceTelemetryLog
    {
        return $this->telemetryManager->recordTelemetry($device, $data);
    }

    public function detectOfflineDevices(): void
    {
        $this->telemetryManager->detectOfflineDevices();
    }

    public function publishFirmware(array $data): FirmwarePackage
    {
        return $this->firmwareManager->publishPackage($data);
    }

    public function installFirmware(Device $device, FirmwarePackage $package): DeviceFirmwareInstallation
    {
        return $this->firmwareManager->install($device, $package);
    }

    public function rollbackFirmware(Device $device, DeviceFirmwareInstallation $installation): void
    {
        $this->firmwareManager->rollback($device, $installation);
    }

    public function queueCommand(
        Device $device,
        string $commandType,
        array $payload,
        ?string $correlationId = null,
        ?string $idempotencyKey = null
    ): DeviceCommand {
        return $this->commandManager->queueCommand($device, $commandType, $payload, $correlationId, $idempotencyKey);
    }

    public function dispatchCommand(DeviceCommand $command): void
    {
        $this->commandManager->dispatchCommand($command);
    }

    public function acknowledgeCommand(DeviceCommand $command): void
    {
        $this->commandManager->acknowledgeCommand($command);
    }

    public function completeCommand(DeviceCommand $command): void
    {
        $this->commandManager->completeCommand($command);
    }

    public function failCommand(DeviceCommand $command, string $error): void
    {
        $this->commandManager->failCommand($command, $error);
    }
}
