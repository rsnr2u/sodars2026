<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\IoT\Domain\Entities\DeviceHealthSnapshot;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;

class DeviceHealthReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'device_health';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $snapshots = DeviceHealthSnapshot::with('device')->get();

        return [
            'summary' => [
                'average_health_score' => $snapshots->avg('overall_health_score') ?? 100,
            ],
            'records' => $snapshots->map(fn(DeviceHealthSnapshot $s) => [
                'device_number' => $s->device?->device_number ?? 'Unknown',
                'overall_health_score' => $s->overall_health_score,
                'cpu_usage_percent' => $s->cpu_usage_percent,
                'memory_usage_percent' => $s->memory_usage_percent,
                'temperature_celsius' => $s->temperature_celsius,
                'last_seen_at' => $s->last_seen_at?->toIso8601String(),
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Device Number', 'Health Score', 'CPU %', 'Memory %', 'Temp (C)', 'Last Seen'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $r) {
            $rows[] = [
                $r['device_number'],
                $r['overall_health_score'],
                $r['cpu_usage_percent'],
                $r['memory_usage_percent'],
                $r['temperature_celsius'],
                $r['last_seen_at']
            ];
        }
        return $rows;
    }
}
