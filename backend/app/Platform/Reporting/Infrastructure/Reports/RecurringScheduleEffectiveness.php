<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use Illuminate\Support\Facades\DB;

class RecurringScheduleEffectiveness implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'operations_recurring_effectiveness';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $records = DB::table('operations_recurrence_rules')
            ->select('id', 'schedule_id', 'frequency', 'interval')
            ->get()->map(fn($item) => (array) $item)->toArray();

        return [
            'summary' => [
                'total_rules' => count($records),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Rule ID', 'Schedule ID', 'Frequency', 'Interval'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $rows[] = [
                $record['id'] ?? '',
                $record['schedule_id'] ?? '',
                $record['frequency'] ?? '',
                $record['interval'] ?? 0,
            ];
        }
        return $rows;
    }
}
