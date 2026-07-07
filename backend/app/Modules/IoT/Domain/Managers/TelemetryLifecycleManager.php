<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Managers;

use App\Modules\IoT\Domain\Entities\Device;
use App\Modules\IoT\Domain\Entities\DeviceHeartbeat;
use App\Modules\IoT\Domain\Entities\DeviceTelemetryLog;
use App\Modules\IoT\Domain\Entities\DeviceHealthSnapshot;
use App\Modules\IoT\Domain\Entities\DeviceAlert;
use App\Modules\IoT\Domain\Enums\DeviceStatus;
use App\Modules\IoT\Domain\Events\DeviceHeartbeatReceived;
use App\Modules\IoT\Domain\Events\DeviceTelemetryReceived;
use App\Modules\IoT\Domain\Events\DeviceTelemetryProcessed;
use App\Modules\IoT\Domain\Events\DeviceOfflineDetected;
use App\Modules\IoT\Domain\Events\DeviceAlertRaised;
use Illuminate\Support\Str;

class TelemetryLifecycleManager
{
    /**
     * Ingest heartbeat check-in.
     */
    public function recordHeartbeat(Device $device, array $data): DeviceHeartbeat
    {
        $heartbeat = DeviceHeartbeat::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $device->organization_id,
            'device_id' => $device->id,
            'received_at' => now(),
            'ip_address' => $data['ip_address'] ?? '127.0.0.1',
            'firmware_version' => $data['firmware_version'] ?? ($device->firmware_version ?? '1.0.0'),
            'signal_quality_dbm' => (int) ($data['signal_quality_dbm'] ?? -70),
            'battery_level_percent' => (int) ($data['battery_level_percent'] ?? 100),
            'uptime_seconds' => (int) ($data['uptime_seconds'] ?? 0),
        ]);

        // Update Device last seen
        $device->update(['last_seen_at' => now()]);

        // Update Health Snapshot projection (rebuilt dynamically from latest data)
        $snapshot = DeviceHealthSnapshot::firstOrCreate(
            ['device_id' => $device->id],
            ['id' => (string) Str::uuid(), 'organization_id' => $device->organization_id]
        );

        $signalScore = $this->calculateSignalScore($heartbeat->signal_quality_dbm);
        $batteryScore = $this->calculateBatteryScore($heartbeat->battery_level_percent);

        $snapshot->update([
            'battery_level_percent' => $heartbeat->battery_level_percent,
            'signal_quality_dbm' => $heartbeat->signal_quality_dbm,
            'battery_score' => $batteryScore,
            'signal_score' => $signalScore,
            'last_seen_at' => now(),
            'overall_health_score' => (int) (($batteryScore + $signalScore + $snapshot->temperature_score + $snapshot->storage_score) / 4),
        ]);

        event(new DeviceHeartbeatReceived($device->id, 1, $heartbeat->toArray()));

        return $heartbeat;
    }

    /**
     * Ingest telemetry log.
     */
    public function recordTelemetry(Device $device, array $data): DeviceTelemetryLog
    {
        $log = DeviceTelemetryLog::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $device->organization_id,
            'device_id' => $device->id,
            'logged_at' => now(),
            'latitude' => isset($data['latitude']) ? (float)$data['latitude'] : null,
            'longitude' => isset($data['longitude']) ? (float)$data['longitude'] : null,
            'speed_kph' => isset($data['speed_kph']) ? (float)$data['speed_kph'] : null,
            'heading_degrees' => isset($data['heading_degrees']) ? (int)$data['heading_degrees'] : null,
            'diagnostics' => $data['diagnostics'] ?? [],
        ]);

        // Update Device last seen
        $device->update(['last_seen_at' => now()]);

        // Update Health Snapshot projection
        $snapshot = DeviceHealthSnapshot::firstOrCreate(
            ['device_id' => $device->id],
            ['id' => (string) Str::uuid(), 'organization_id' => $device->organization_id]
        );

        $cpu = (int) ($data['diagnostics']['cpu_usage_percent'] ?? $snapshot->cpu_usage_percent ?? 10);
        $mem = (int) ($data['diagnostics']['memory_usage_percent'] ?? $snapshot->memory_usage_percent ?? 15);
        $disk = (int) ($data['diagnostics']['disk_usage_percent'] ?? $snapshot->disk_usage_percent ?? 20);
        $temp = (int) ($data['diagnostics']['temperature_celsius'] ?? $snapshot->temperature_celsius ?? 35);

        $tempScore = $this->calculateTemperatureScore($temp);
        $storageScore = $this->calculateStorageScore($disk);

        $snapshot->update([
            'cpu_usage_percent' => $cpu,
            'memory_usage_percent' => $mem,
            'disk_usage_percent' => $disk,
            'temperature_celsius' => $temp,
            'temperature_score' => $tempScore,
            'storage_score' => $storageScore,
            'last_seen_at' => now(),
            'overall_health_score' => (int) (($snapshot->battery_score + $snapshot->signal_score + $tempScore + $storageScore) / 4),
        ]);

        event(new DeviceTelemetryReceived($device->id, 1, $log->toArray()));
        event(new DeviceTelemetryProcessed($device->id, 1, $log->toArray()));

        return $log;
    }

    /**
     * Scan and detect stale/offline devices.
     */
    public function detectOfflineDevices(): void
    {
        $staleTime = now()->subMinutes(10);
        $devices = Device::where('status', DeviceStatus::Active)
            ->where(function ($query) use ($staleTime) {
                $query->whereNull('last_seen_at')
                      ->orWhere('last_seen_at', '<=', $staleTime);
            })
            ->get();

        foreach ($devices as $device) {
            $device->update(['status' => DeviceStatus::Offline]);

            // Raise warning alert
            $alert = DeviceAlert::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $device->organization_id,
                'device_id' => $device->id,
                'alert_type' => 'Device Offline',
                'severity' => 'critical',
                'message' => "Device [{$device->device_number}] has missed consecutive check-ins.",
                'raised_at' => now(),
            ]);

            event(new DeviceOfflineDetected($device->id, 1, $device->toArray()));
            event(new DeviceAlertRaised($device->id, 1, $alert->toArray()));
        }
    }

    protected function calculateSignalScore(int $dbm): int
    {
        if ($dbm >= -50) return 100;
        if ($dbm <= -110) return 0;
        return (int) (($dbm + 110) * (100 / 60));
    }

    protected function calculateBatteryScore(int $percent): int
    {
        return max(0, min(100, $percent));
    }

    protected function calculateTemperatureScore(int $temp): int
    {
        if ($temp <= 65) return 100;
        if ($temp >= 85) return 0;
        return (int) ((85 - $temp) * 5);
    }

    protected function calculateStorageScore(int $diskPercent): int
    {
        return max(0, min(100, 100 - $diskPercent));
    }
}
