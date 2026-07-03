<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use Illuminate\Support\Facades\DB;

class InventoryOccupancyReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'inventory_occupancy';
    }

    public static function getParameterSchema(): array
    {
        return [
            'status' => 'nullable|string',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $status = $parameters->getString('status');

        $totalQuery = DB::table('inventory_availability');
        if (!empty($status)) {
            $totalQuery->where('availability_status', $status);
        }

        $totalSlots = $totalQuery->count();

        $occupiedSlots = DB::table('inventory_availability')
            ->whereIn('availability_status', ['blocked', 'booked'])
            ->count();

        $occupancyRate = $totalSlots > 0 ? round(($occupiedSlots / $totalSlots) * 100, 2) : 0.0;

        $records = DB::table('inventory_availability')
            ->join('inventory_faces', 'inventory_availability.inventory_face_id', '=', 'inventory_faces.id')
            ->join('inventories', 'inventory_faces.inventory_id', '=', 'inventories.id')
            ->select(
                'inventories.display_name as inventory_name',
                'inventories.inventory_code',
                'inventory_faces.face_name',
                'inventory_availability.availability_status',
                'inventory_availability.start_date',
                'inventory_availability.end_date'
            )
            ->take(100)
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();

        return [
            'summary' => [
                'total_slots' => $totalSlots,
                'occupied_slots' => $occupiedSlots,
                'occupancy_rate_percentage' => $occupancyRate,
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Inventory Code', 'Inventory Name', 'Face Name', 'Status', 'Start Date', 'End Date'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $rows[] = [
                $record['inventory_code'] ?? '',
                $record['inventory_name'] ?? '',
                $record['face_name'] ?? '',
                $record['availability_status'] ?? '',
                $record['start_date'] ?? '',
                $record['end_date'] ?? '',
            ];
        }
        return $rows;
    }
}
