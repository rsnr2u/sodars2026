<?php

declare(strict_types=1);

namespace App\Modules\IoT\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\IoT\Domain\Entities\Device;
use App\Modules\IoT\Domain\Entities\FirmwarePackage;
use App\Modules\IoT\Domain\Entities\DeviceFirmwareInstallation;
use App\Modules\IoT\Domain\Services\DeviceLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FirmwareController extends BaseApiController
{
    public function __construct(protected DeviceLifecycleService $lifecycleService) {}

    public function publish(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organization_id' => 'required|uuid',
            'version' => 'required|string',
            'sha256' => 'required|string|size:64',
            'size_bytes' => 'required|integer',
            'signature' => 'required|string',
            'signature_algorithm' => 'nullable|string',
            'download_url' => 'required|string',
            'compatible_device_types' => 'required|array',
        ]);

        $package = $this->lifecycleService->publishFirmware($validated);

        return response()->json([
            'message' => 'Firmware published successfully.',
            'package' => $package,
        ], 201);
    }

    public function rollout(string $deviceId, Request $request): JsonResponse
    {
        $device = Device::findOrFail($deviceId);
        $validated = $request->validate([
            'firmware_package_id' => 'required|uuid',
        ]);

        $package = FirmwarePackage::findOrFail($validated['firmware_package_id']);
        $installation = $this->lifecycleService->installFirmware($device, $package);

        return response()->json([
            'message' => 'Rollout installation scheduled.',
            'installation' => $installation,
        ], 202);
    }

    public function rollback(string $installationId): JsonResponse
    {
        $installation = DeviceFirmwareInstallation::findOrFail($installationId);
        $device = Device::findOrFail($installation->device_id);

        $this->lifecycleService->rollbackFirmware($device, $installation);

        return response()->json(['message' => 'Firmware installation rolled back.']);
    }
}
