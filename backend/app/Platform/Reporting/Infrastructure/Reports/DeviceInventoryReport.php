<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\IoT\Domain\Entities\Device;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;

class DeviceInventoryReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'device_inventory';
    }

    public static function getParameterSchema(): array
    {
        return [
            'device_type' => 'nullable|string',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $type = $parameters->getString('device_type');
        $query = Device::query();

        if (!empty($type)) {
            $query->where('device_type', $type);
        }

        $devices = $query->get();

        return [
            'summary' => [
                'total_devices' => $devices->count(),
                'active_devices' => $devices->where('status.value', 'Active')->count(),
            ],
            'records' => $devices->map(fn(Device $d) => [
                'id' => $d->id,
                'device_number' => $d->device_number,
                'serial_number' => $d->serial_number,
                'name' => $d->name,
                'device_type' => $d->device_type->value,
                'status' => $d->status->value,
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Device Number', 'Serial Number', 'Name', 'Type', 'Status'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $r) {
            $rows[] = [$r['device_number'], $r['serial_number'], $r['name'], $r['device_type'], $r['status']];
        }
        return $rows;
    }
}
