<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\IoT\Domain\Entities\DeviceHeartbeat;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;

class DeviceUptimeReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'device_uptime';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $heartbeats = DeviceHeartbeat::with('device')->get();

        return [
            'records' => $heartbeats->map(fn(DeviceHeartbeat $h) => [
                'device_number' => $h->device?->device_number ?? 'Unknown',
                'ip_address' => $h->ip_address,
                'uptime_seconds' => $h->uptime_seconds,
                'received_at' => $h->received_at?->toIso8601String(),
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Device Number', 'IP Address', 'Uptime Seconds', 'Heartbeat Time'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $r) {
            $rows[] = [$r['device_number'], $r['ip_address'], $r['uptime_seconds'], $r['received_at']];
        }
        return $rows;
    }
}
