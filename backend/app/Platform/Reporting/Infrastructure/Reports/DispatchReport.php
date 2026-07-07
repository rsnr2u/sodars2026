<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use Illuminate\Support\Facades\DB;

class DispatchReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'operations_dispatch';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $records = DB::table('operations_dispatch_progress_projections')
            ->select('id', 'schedule_id', 'execution_id', 'completion_percentage', 'eta_estimate', 'created_at')
            ->get()->map(fn($item) => (array) $item)->toArray();

        return [
            'summary' => [
                'total_dispatches' => count($records),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['ID', 'Schedule ID', 'Execution ID', 'Completion %', 'ETA Estimate', 'Created At'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $rows[] = [
                $record['id'] ?? '',
                $record['schedule_id'] ?? '',
                $record['execution_id'] ?? '',
                $record['completion_percentage'] ?? 0,
                $record['eta_estimate'] ?? '',
                $record['created_at'] ?? '',
            ];
        }
        return $rows;
    }
}
