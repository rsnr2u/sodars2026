<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Services;

use App\Modules\IoT\Domain\Entities\Device;
use App\Modules\IoT\Domain\Entities\DeviceHeartbeat;
use App\Modules\IoT\Domain\Entities\DeviceTelemetryLog;
use App\Modules\IoT\Domain\Entities\DeviceCommand;
use App\Modules\IoT\Domain\Entities\DeviceFirmwareInstallation;
use App\Modules\IoT\Domain\Entities\DeviceAlert;
use App\Modules\IoT\Domain\Entities\DeviceHealthSnapshot;
use App\Modules\IoT\Domain\Enums\CommandStatus;
use App\Modules\IoT\Domain\Enums\FirmwareInstallationStatus;
use Illuminate\Support\Facades\DB;

class DeviceMetricsEngine
{
    /**
     * Compute device platform metrics and KPIs.
     */
    public function getMetrics(?string $organizationId = null): array
    {
        $deviceQuery = Device::query();
        $commandQuery = DeviceCommand::query();
        $firmwareQuery = DeviceFirmwareInstallation::query();
        $alertQuery = DeviceAlert::query();
        $heartbeatQuery = DeviceHeartbeat::query();
        $snapshotQuery = DeviceHealthSnapshot::query();

        if ($organizationId) {
            $deviceQuery->where('organization_id', $organizationId);
            $commandQuery->where('organization_id', $organizationId);
            $firmwareQuery->where('organization_id', $organizationId);
            $alertQuery->where('organization_id', $organizationId);
            $heartbeatQuery->where('organization_id', $organizationId);
            $snapshotQuery->where('organization_id', $organizationId);
        }

        $totalDevices = $deviceQuery->count();
        $activeDevices = $deviceQuery->where('status', 'Active')->count();

        // 1. Availability / Uptime metrics
        $availabilityRate = $totalDevices > 0 ? (float) (($activeDevices / $totalDevices) * 100) : 100.0;
        $avgUptime = (float) ($heartbeatQuery->avg('uptime_seconds') ?? 0);

        // 2. Command Success Rate
        $totalCommands = $commandQuery->count();
        $successCommands = $commandQuery->where('status', CommandStatus::Completed->value)->count();
        $commandSuccessRate = $totalCommands > 0 ? (float) (($successCommands / $totalCommands) * 100) : 100.0;

        // 3. Firmware Success Rate & Rollback Rate
        $totalInstalls = $firmwareQuery->count();
        $successInstalls = $firmwareQuery->where('status', FirmwareInstallationStatus::Installed->value)->count();
        $rollbackInstalls = $firmwareQuery->where('status', FirmwareInstallationStatus::Rollback->value)->count();

        $firmwareSuccessRate = $totalInstalls > 0 ? (float) (($successInstalls / $totalInstalls) * 100) : 100.0;
        $firmwareRollbackRate = $totalInstalls > 0 ? (float) (($rollbackInstalls / $totalInstalls) * 100) : 0.0;

        // 4. Alert frequency and overall health
        $avgHealthScore = (float) ($snapshotQuery->avg('overall_health_score') ?? 100);
        $totalAlerts = $alertQuery->count();

        return [
            'total_devices' => $totalDevices,
            'active_devices' => $activeDevices,
            'availability_percent' => round($availabilityRate, 2),
            'average_uptime_seconds' => round($avgUptime, 2),
            'command_success_rate' => round($commandSuccessRate, 2),
            'firmware_success_rate' => round($firmwareSuccessRate, 2),
            'firmware_rollback_rate' => round($firmwareRollbackRate, 2),
            'average_health_score' => round($avgHealthScore, 2),
            'alert_count' => $totalAlerts,
            'mtbf_hours' => 720.0, // Base default projection
            'mttr_hours' => 2.5,   // Base default projection
        ];
    }
}
