<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\IoT\Domain\Entities\DeviceTelemetryLog;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;

class TelemetryStatisticsReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'telemetry_statistics';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $logs = DeviceTelemetryLog::with('device')->get();

        return [
            'records' => $logs->map(fn(DeviceTelemetryLog $l) => [
                'device_number' => $l->device?->device_number ?? 'Unknown',
                'logged_at' => $l->logged_at?->toIso8601String(),
                'latitude' => $l->latitude,
                'longitude' => $l->longitude,
                'speed_kph' => $l->speed_kph,
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Device Number', 'Logged At', 'Latitude', 'Longitude', 'Speed (kph)'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $r) {
            $rows[] = [$r['device_number'], $r['logged_at'], $r['latitude'], $r['longitude'], $r['speed_kph']];
        }
        return $rows;
    }
}
