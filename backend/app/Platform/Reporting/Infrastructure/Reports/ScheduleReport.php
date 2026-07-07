<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use Illuminate\Support\Facades\DB;

class ScheduleReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'operations_schedule';
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

        $query = DB::table('operations_schedules');
        if (!empty($status)) {
            $query->where('status', $status);
        }

        $records = $query->select(
            'id',
            'schedule_number',
            'name',
            'schedule_type',
            'status',
            'start_time',
            'end_time',
            'created_at'
        )->get()->map(fn($item) => (array) $item)->toArray();

        return [
            'summary' => [
                'total_schedules' => count($records),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Schedule ID', 'Schedule Number', 'Name', 'Type', 'Status', 'Start Time', 'End Time', 'Created At'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $rows[] = [
                $record['id'] ?? '',
                $record['schedule_number'] ?? '',
                $record['name'] ?? '',
                $record['schedule_type'] ?? '',
                $record['status'] ?? '',
                $record['start_time'] ?? '',
                $record['end_time'] ?? '',
                $record['created_at'] ?? '',
            ];
        }
        return $rows;
    }
}
