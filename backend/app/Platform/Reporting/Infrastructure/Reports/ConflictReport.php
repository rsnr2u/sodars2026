<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use Illuminate\Support\Facades\DB;

class ConflictReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'operations_conflict';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $records = DB::table('operations_schedule_conflicts')
            ->select('id', 'schedule_id', 'conflict_type', 'severity', 'message', 'detected_at', 'resolved_at')
            ->get()->map(fn($item) => (array) $item)->toArray();

        return [
            'summary' => [
                'total_conflicts' => count($records),
                'resolved_conflicts' => collect($records)->whereNotNull('resolved_at')->count(),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Conflict ID', 'Schedule ID', 'Type', 'Severity', 'Message', 'Detected At', 'Resolved At'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $rows[] = [
                $record['id'] ?? '',
                $record['schedule_id'] ?? '',
                $record['conflict_type'] ?? '',
                $record['severity'] ?? '',
                $record['message'] ?? '',
                $record['detected_at'] ?? '',
                $record['resolved_at'] ?? '',
            ];
        }
        return $rows;
    }
}
