<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\IoT\Domain\Entities\Device;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;

class FirmwareComplianceReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'firmware_compliance';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $devices = Device::all();

        return [
            'records' => $devices->map(fn(Device $d) => [
                'device_number' => $d->device_number,
                'name' => $d->name,
                'device_type' => $d->device_type->value,
                'installed_version' => $d->firmware_version ?? 'Unknown',
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Device Number', 'Name', 'Type', 'Installed Firmware Version'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $r) {
            $rows[] = [$r['device_number'], $r['name'], $r['device_type'], $r['installed_version']];
        }
        return $rows;
    }
}
