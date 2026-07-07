<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use Illuminate\Support\Facades\DB;

class ScheduleTimelineReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'operations_schedule_timeline';
    }

    public static function getParameterSchema(): array
    {
        return [
            'schedule_id' => 'required|string',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $schedId = $parameters->getString('schedule_id');

        $records = DB::table('operations_schedule_timelines')
            ->where('schedule_id', $schedId)
            ->select('id', 'schedule_id', 'event_name', 'description', 'occurred_at')
            ->orderBy('occurred_at', 'asc')
            ->get()->map(fn($item) => (array) $item)->toArray();

        return [
            'summary' => [
                'total_timeline_events' => count($records),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Timeline ID', 'Schedule ID', 'Event Name', 'Description', 'Occurred At'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $rows[] = [
                $record['id'] ?? '',
                $record['schedule_id'] ?? '',
                $record['event_name'] ?? '',
                $record['description'] ?? '',
                $record['occurred_at'] ?? '',
            ];
        }
        return $rows;
    }
}
