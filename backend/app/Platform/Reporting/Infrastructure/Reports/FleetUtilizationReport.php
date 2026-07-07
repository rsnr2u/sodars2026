<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\Transport\Domain\Entities\Vehicle;
use App\Modules\Transport\Domain\Enums\VehicleStatus;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Identity\Application\Services\IdentityContext;

class FleetUtilizationReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'fleet_utilization';
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

        return [
            'summary' => [
                'total_vehicles' => $records->count(),
                'active_vehicles' => $records->where('status', VehicleStatus::Active)->count(),
                'maintenance_vehicles' => $records->where('status', VehicleStatus::Maintenance)->count(),
                'inactive_vehicles' => $records->where('status', VehicleStatus::Inactive)->count(),
            ],
            'records' => $records->map(fn(Vehicle $v) => [
                'id' => $v->id,
                'vehicle_number' => $v->vehicle_number,
                'license_plate' => $v->license_plate,
                'make' => $v->make,
                'model' => $v->model,
                'year' => $v->year,
                'status' => $v->status->value ?? $v->status,
                'current_odometer' => $v->current_odometer,
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['ID', 'Vehicle Number', 'License Plate', 'Make', 'Model', 'Year', 'Status', 'Odometer'];
    }

    public function getExportRows(array $data): array
    {
        return array_map(fn($r) => [
            $r['id'],
            $r['vehicle_number'],
            $r['license_plate'],
            $r['make'],
            $r['model'],
            $r['year'],
            $r['status'],
            $r['current_odometer'],
        ], $data['records'] ?? []);
    }
}
