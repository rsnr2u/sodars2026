<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Modules\Campaigns\Domain\Entities\CampaignSchedule;
use App\Modules\Inventory\Domain\Entities\InventoryFace;

class CampaignOccupancyReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'campaign_occupancy';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        // Query campaign schedules under active tenant
        $schedules = CampaignSchedule::all();
        $totalBookedSlots = $schedules->count();

        // Get total active inventory faces in system to determine capacity
        $totalFacesCount = InventoryFace::where('is_active', true)->count();
        if ($totalFacesCount === 0) {
            $totalFacesCount = 1; // avoid division by zero
        }

        // Distinct days scheduled
        $uniqueDates = $schedules->pluck('date')->map(fn($d) => $d?->toDateString())->unique()->filter();
        $totalDays = $uniqueDates->count();
        if ($totalDays === 0) {
            $totalDays = 1;
        }

        // Each face has 6 slots per day
        $totalCapacitySlots = $totalFacesCount * $totalDays * 6;

        $fillRate = round(($totalBookedSlots / $totalCapacitySlots) * 100, 2);

        $records = $schedules->groupBy('inventory_face_id')->map(function ($items, $faceId) use ($totalDays) {
            $bookedCount = $items->count();
            $faceCapacity = $totalDays * 6;
            return [
                'inventory_face_id' => $faceId,
                'booked_slots' => $bookedCount,
                'utilization_percent' => round(($bookedCount / $faceCapacity) * 100, 2),
            ];
        })->values()->toArray();

        return [
            'summary' => [
                'total_booked_slots' => $totalBookedSlots,
                'total_days' => $totalDays,
                'total_faces' => $totalFacesCount,
                'fill_rate_percent' => $fillRate,
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Inventory Face ID', 'Booked Slots', 'Utilization %'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $rec) {
            $rows[] = [
                $rec['inventory_face_id'],
                $rec['booked_slots'],
                $rec['utilization_percent'],
            ];
        }
        return $rows;
    }
}
