<?php

declare(strict_types=1);

namespace App\Modules\IoT\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\IoT\Domain\Entities\Device;
use App\Modules\IoT\Domain\Entities\DeviceCommand;
use App\Modules\IoT\Domain\Services\DeviceLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommandController extends BaseApiController
{
    public function __construct(protected DeviceLifecycleService $lifecycleService) {}

    public function queue(string $deviceId, Request $request): JsonResponse
    {
        $device = Device::findOrFail($deviceId);
        $validated = $request->validate([
            'command_type' => 'required|string',
            'payload' => 'required|array',
            'correlation_id' => 'nullable|string',
            'idempotency_key' => 'nullable|string',
        ]);

        $command = $this->lifecycleService->queueCommand(
            $device,
            $validated['command_type'],
            $validated['payload'],
            $validated['correlation_id'] ?? null,
            $validated['idempotency_key'] ?? null
        );

        return response()->json([
            'message' => 'Command queued successfully.',
            'command' => $command,
        ], 202);
    }

    public function acknowledge(string $commandId): JsonResponse
    {
        $command = DeviceCommand::findOrFail($commandId);
        $this->lifecycleService->acknowledgeCommand($command);

        return response()->json(['message' => 'Command acknowledged by device.']);
    }

    public function complete(string $commandId): JsonResponse
    {
        $command = DeviceCommand::findOrFail($commandId);
        $this->lifecycleService->completeCommand($command);

        return response()->json(['message' => 'Command marked as completed.']);
    }

    public function status(string $commandId): JsonResponse
    {
        $command = DeviceCommand::findOrFail($commandId);

        return response()->json($command);
    }
}
