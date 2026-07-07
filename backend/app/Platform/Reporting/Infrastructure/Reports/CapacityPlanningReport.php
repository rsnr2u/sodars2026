<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use Illuminate\Support\Facades\DB;

class CapacityPlanningReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'operations_capacity_planning';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $records = DB::table('operations_resource_workload_projections')
            ->select('id', 'resource_id', 'assigned_schedules_count', 'total_allocated_seconds', 'utilization_score')
            ->get()->map(fn($item) => (array) $item)->toArray();

        return [
            'summary' => [
                'total_monitored_resources' => count($records),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['ID', 'Resource ID', 'Assigned Count', 'Total Seconds', 'Utilization Score'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $rows[] = [
                $record['id'] ?? '',
                $record['resource_id'] ?? '',
                $record['assigned_schedules_count'] ?? 0,
                $record['total_allocated_seconds'] ?? 0,
                $record['utilization_score'] ?? 0,
            ];
        }
        return $rows;
    }
}
