<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use Illuminate\Support\Facades\DB;

class ResourceUtilizationReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'operations_resource_utilization';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $records = DB::table('operations_resource_workload_projections')
            ->select('resource_id', 'total_allocated_seconds', 'utilization_score')
            ->get()->map(fn($item) => (array) $item)->toArray();

        return [
            'summary' => [
                'total_monitored' => count($records),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Resource ID', 'Allocated Seconds', 'Utilization Score'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $rows[] = [
                $record['resource_id'] ?? '',
                $record['total_allocated_seconds'] ?? 0,
                $record['utilization_score'] ?? 0,
            ];
        }
        return $rows;
    }
}
