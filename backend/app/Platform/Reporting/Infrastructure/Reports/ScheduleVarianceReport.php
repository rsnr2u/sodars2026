<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use Illuminate\Support\Facades\DB;

class ScheduleVarianceReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'operations_schedule_variance';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $records = DB::table('operations_schedule_executions')
            ->select('id', 'schedule_id', 'execution_status', 'actual_duration_seconds')
            ->get()->map(fn($item) => (array) $item)->toArray();

        return [
            'summary' => [
                'total_variance_records' => count($records),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Execution ID', 'Schedule ID', 'Status', 'Duration Seconds'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $rows[] = [
                $record['id'] ?? '',
                $record['schedule_id'] ?? '',
                $record['execution_status'] ?? '',
                $record['actual_duration_seconds'] ?? 0,
            ];
        }
        return $rows;
    }
}
