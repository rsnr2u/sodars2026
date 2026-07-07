<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\Transport\Domain\Entities\VehicleMaintenance;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Identity\Application\Services\IdentityContext;

class FleetMaintenanceReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'fleet_maintenance';
    }

    public static function getParameterSchema(): array
    {
        return [
            'vehicle_id' => 'nullable|string',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $vehicleId = $parameters->getString('vehicle_id');
        $query = VehicleMaintenance::query();

        // Multi-tenant scope
        $orgId = IdentityContext::organizationId();
        if ($orgId) {
            $query->where('organization_id', $orgId);
        }

        if (!empty($vehicleId)) {
            $query->where('vehicle_id', $vehicleId);
        }

        $records = $query->with('vehicle')->orderBy('maintenance_date', 'desc')->get();

        return [
            'summary' => [
                'total_records' => $records->count(),
                'total_cost_cents' => (int) $records->sum('cost_cents'),
            ],
            'records' => $records->map(fn(VehicleMaintenance $m) => [
                'id' => $m->id,
                'vehicle_number' => $m->vehicle?->vehicle_number,
                'maintenance_type' => $m->maintenance_type,
                'description' => $m->description,
                'cost_cents' => $m->cost_cents,
                'maintenance_date' => $m->maintenance_date?->toDateString(),
                'odometer_reading' => $m->odometer_reading,
                'status' => $m->status,
                'next_due_date' => $m->next_due_date?->toDateString(),
                'next_due_odometer' => $m->next_due_odometer,
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['ID', 'Vehicle Number', 'Type', 'Description', 'Cost Cents', 'Date', 'Odometer', 'Status', 'Next Due Date', 'Next Due Odometer'];
    }

    public function getExportRows(array $data): array
    {
        return array_map(fn($r) => [
            $r['id'],
            $r['vehicle_number'],
            $r['maintenance_type'],
            $r['description'],
            $r['cost_cents'],
            $r['maintenance_date'],
            $r['odometer_reading'],
            $r['status'],
            $r['next_due_date'],
            $r['next_due_odometer'],
        ], $data['records'] ?? []);
    }
}
