<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use Illuminate\Support\Facades\DB;

class CalendarReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'operations_calendar';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $records = DB::table('operations_calendars')
            ->select('id', 'name', 'type', 'timezone', 'created_at')
            ->get()->map(fn($item) => (array) $item)->toArray();

        return [
            'summary' => [
                'total_calendars' => count($records),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Calendar ID', 'Name', 'Type', 'Timezone', 'Created At'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $rows[] = [
                $record['id'] ?? '',
                $record['name'] ?? '',
                $record['type'] ?? '',
                $record['timezone'] ?? '',
                $record['created_at'] ?? '',
            ];
        }
        return $rows;
    }
}
