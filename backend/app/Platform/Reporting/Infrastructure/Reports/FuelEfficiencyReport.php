<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\Transport\Domain\Entities\VehicleFuelLog;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Identity\Application\Services\IdentityContext;

class FuelEfficiencyReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'fuel_efficiency';
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
        $query = VehicleFuelLog::query();

        // Multi-tenant scope
        $orgId = IdentityContext::organizationId();
        if ($orgId) {
            $query->where('organization_id', $orgId);
        }

        if (!empty($vehicleId)) {
            $query->where('vehicle_id', $vehicleId);
        }

        $records = $query->with('vehicle')->orderBy('fuel_date', 'desc')->get();

        return [
            'summary' => [
                'total_refills' => $records->count(),
                'total_liters' => (float) $records->sum('liters'),
                'total_cost_cents' => (int) $records->sum('cost_cents'),
            ],
            'records' => $records->map(fn(VehicleFuelLog $f) => [
                'id' => $f->id,
                'vehicle_number' => $f->vehicle?->vehicle_number,
                'fuel_date' => $f->fuel_date?->toDateString(),
                'liters' => $f->liters,
                'cost_cents' => $f->cost_cents,
                'odometer_reading' => $f->odometer_reading,
                'fuel_station' => $f->fuel_station,
                'payment_method' => $f->payment_method,
                'filled_by' => $f->filled_by,
                'receipt_number' => $f->receipt_number,
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['ID', 'Vehicle Number', 'Fuel Date', 'Liters', 'Cost Cents', 'Odometer', 'Station', 'Payment Method', 'Filled By', 'Receipt'];
    }

    public function getExportRows(array $data): array
    {
        return array_map(fn($r) => [
            $r['id'],
            $r['vehicle_number'],
            $r['fuel_date'],
            $r['liters'],
            $r['cost_cents'],
            $r['odometer_reading'],
            $r['fuel_station'],
            $r['payment_method'],
            $r['filled_by'],
            $r['receipt_number'],
        ], $data['records'] ?? []);
    }
}
