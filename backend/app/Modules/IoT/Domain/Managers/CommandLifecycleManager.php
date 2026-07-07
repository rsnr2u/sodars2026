<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Managers;

use App\Modules\IoT\Domain\Entities\Device;
use App\Modules\IoT\Domain\Entities\DeviceCommand;
use App\Modules\IoT\Domain\Enums\CommandStatus;
use App\Modules\IoT\Domain\Events\DeviceCommandQueued;
use App\Modules\IoT\Domain\Events\DeviceCommandDispatched;
use App\Modules\IoT\Domain\Events\DeviceCommandAcknowledged;
use App\Modules\IoT\Domain\Events\DeviceCommandCompleted;
use App\Modules\IoT\Domain\Events\DeviceCommandFailed;
use Illuminate\Support\Str;

class CommandLifecycleManager
{
    /**
     * Queue a new command to the target device. Enforces strict idempotency.
     */
    public function queueCommand(
        Device $device,
        string $commandType,
        array $payload,
        ?string $correlationId = null,
        ?string $idempotencyKey = null
    ): DeviceCommand {
        $idempotencyKey = $idempotencyKey ?? 'cmd_' . Str::random(32);

        // Check Idempotency Key to avoid duplicate processing
        $existing = DeviceCommand::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing;
        }

        $command = DeviceCommand::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $device->organization_id,
            'device_id' => $device->id,
            'command_uuid' => (string) Str::uuid(),
            'idempotency_key' => $idempotencyKey,
            'correlation_id' => $correlationId,
            'command_type' => $commandType,
            'status' => CommandStatus::Queued,
            'payload' => $payload,
            'attempts' => 0,
        ]);

        event(new DeviceCommandQueued($device->id, 1, $command->toArray()));

        return $command;
    }

    /**
     * Dispatch the command.
     */
    public function dispatchCommand(DeviceCommand $command): void
    {
        $command->update([
            'status' => CommandStatus::Dispatched,
            'last_attempted_at' => now(),
            'attempts' => $command->attempts + 1,
        ]);

        event(new DeviceCommandDispatched($command->device_id, 1, $command->toArray()));
    }

    /**
     * Acknowledge command receipt from the device.
     */
    public function acknowledgeCommand(DeviceCommand $command): void
    {
        $command->update([
            'status' => CommandStatus::Acknowledged,
        ]);

        event(new DeviceCommandAcknowledged($command->device_id, 1, $command->toArray()));
    }

    /**
     * Complete the command.
     */
    public function completeCommand(DeviceCommand $command): void
    {
        $command->update([
            'status' => CommandStatus::Completed,
            'completed_at' => now(),
        ]);

        event(new DeviceCommandCompleted($command->device_id, 1, $command->toArray()));
    }

    /**
     * Fail execution.
     */
    public function failCommand(DeviceCommand $command, string $error): void
    {
        $maxAttempts = 3;
        $status = ($command->attempts >= $maxAttempts) ? CommandStatus::Failed : CommandStatus::Queued;

        $command->update([
            'status' => $status,
            'last_error' => $error,
        ]);

        event(new DeviceCommandFailed($command->device_id, 1, $command->toArray()));
    }
}
