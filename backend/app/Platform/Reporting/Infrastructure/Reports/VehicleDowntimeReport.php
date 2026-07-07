<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\Transport\Domain\Entities\Vehicle;
use App\Modules\Transport\Domain\Enums\VehicleStatus;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Identity\Application\Services\IdentityContext;

class VehicleDowntimeReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'vehicle_downtime';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $query = Vehicle::query();

        // Multi-tenant scope
        $orgId = IdentityContext::organizationId();
        if ($orgId) {
            $query->where('organization_id', $orgId);
        }

        $records = $query->get();

        $downtimeRecords = $records->map(function (Vehicle $v) {
            // Calculate mock or real downtime metrics
            $downtimeDays = 0;
            if ($v->status === VehicleStatus::Maintenance) {
                $downtimeDays = 5; // mock active downtime
            } elseif ($v->status === VehicleStatus::Inactive) {
                $downtimeDays = 15;
            }

            return [
                'id' => $v->id,
                'vehicle_number' => $v->vehicle_number,
                'license_plate' => $v->license_plate,
                'make' => $v->make,
                'model' => $v->model,
                'status' => $v->status->value ?? $v->status,
                'downtime_days' => $downtimeDays,
                'vehicle_health_score' => $v->vehicle_health_score ?? 100.0,
            ];
        });

        return [
            'summary' => [
                'total_downtime_vehicles' => $downtimeRecords->where('downtime_days', '>', 0)->count(),
                'total_downtime_days' => (int) $downtimeRecords->sum('downtime_days'),
            ],
            'records' => $downtimeRecords->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['ID', 'Vehicle Number', 'License Plate', 'Make', 'Model', 'Status', 'Downtime Days', 'Health Score'];
    }

    public function getExportRows(array $data): array
    {
        return array_map(fn($r) => [
            $r['id'],
            $r['vehicle_number'],
            $r['license_plate'],
            $r['make'],
            $r['model'],
            $r['status'],
            $r['downtime_days'],
            $r['vehicle_health_score'],
        ], $data['records'] ?? []);
    }
}
