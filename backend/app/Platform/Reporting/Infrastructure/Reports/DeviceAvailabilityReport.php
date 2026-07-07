<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\IoT\Domain\Entities\Device;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;

class DeviceAvailabilityReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'device_availability';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $devices = Device::all();
        $total = $devices->count();
        $active = $devices->where('status.value', 'Active')->count();

        return [
            'summary' => [
                'total_devices' => $total,
                'active_devices' => $active,
                'availability_percent' => $total > 0 ? ($active / $total) * 100 : 100,
            ],
            'records' => $devices->map(fn(Device $d) => [
                'device_number' => $d->device_number,
                'status' => $d->status->value,
                'last_seen_at' => $d->last_seen_at?->toIso8601String(),
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Device Number', 'Status', 'Last Seen'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $r) {
            $rows[] = [$r['device_number'], $r['status'], $r['last_seen_at']];
        }
        return $rows;
    }
}
