<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use Illuminate\Support\Facades\DB;

class PlannedVsActualReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'operations_planned_vs_actual';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $records = DB::table('operations_schedule_executions')
            ->join('operations_schedules', 'operations_schedule_executions.schedule_id', '=', 'operations_schedules.id')
            ->select(
                'operations_schedules.schedule_number',
                'operations_schedules.start_time as planned_start',
                'operations_schedules.end_time as planned_end',
                'operations_schedule_executions.actual_start_time',
                'operations_schedule_executions.actual_end_time'
            )->get()->map(fn($item) => (array) $item)->toArray();

        return [
            'summary' => [
                'total_records' => count($records),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Schedule Number', 'Planned Start', 'Planned End', 'Actual Start', 'Actual End'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $rows[] = [
                $record['schedule_number'] ?? '',
                $record['planned_start'] ?? '',
                $record['planned_end'] ?? '',
                $record['actual_start'] ?? '',
                $record['actual_end'] ?? '',
            ];
        }
        return $rows;
    }
}
