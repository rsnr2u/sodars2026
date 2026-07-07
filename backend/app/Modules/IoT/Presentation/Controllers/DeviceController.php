<?php

declare(strict_types=1);

namespace App\Modules\IoT\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\IoT\Domain\Entities\Device;
use App\Modules\IoT\Domain\Services\DeviceLifecycleService;
use App\Modules\IoT\Domain\Services\DeviceMetricsEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends BaseApiController
{
    public function __construct(
        protected DeviceLifecycleService $lifecycleService,
        protected DeviceMetricsEngine $metricsEngine
    ) {}

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organization_id' => 'required|uuid',
            'serial_number' => 'required|string|unique:devices,serial_number',
            'name' => 'required|string|max:150',
            'device_type' => 'required|string',
            'imei' => 'nullable|string',
            'iccid' => 'nullable|string',
            'mac_address' => 'nullable|string',
            'manufacturer' => 'nullable|string',
            'hardware_revision' => 'nullable|string',
            'firmware_version' => 'nullable|string',
            'device_secret' => 'nullable|string',
        ]);

        $device = $this->lifecycleService->registerDevice($validated);

        return response()->json([
            'message' => 'Device registered successfully.',
            'device' => $device,
        ], 201);
    }

    public function activate(string $id): JsonResponse
    {
        $device = Device::findOrFail($id);
        $this->lifecycleService->activateDevice($device);

        return response()->json(['message' => 'Device activated successfully.']);
    }

    public function assign(string $id, Request $request): JsonResponse
    {
        $device = Device::findOrFail($id);
        $validated = $request->validate([
            'assignable_type' => 'required|string',
            'assignable_id' => 'required|uuid',
            'assigned_by' => 'required|uuid',
        ]);

        $this->lifecycleService->assignDevice(
            $device,
            $validated['assignable_type'],
            $validated['assignable_id'],
            $validated['assigned_by']
        );

        return response()->json(['message' => 'Device assigned successfully.']);
    }

    public function metrics(Request $request): JsonResponse
    {
        $orgId = $request->query('organization_id');
        $metrics = $this->metricsEngine->getMetrics($orgId ? (string) $orgId : null);

        return response()->json($metrics);
    }
}
