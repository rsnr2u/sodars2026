<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use Illuminate\Support\Facades\DB;

class ResourceIdleTimeReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'operations_resource_idle_time';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $records = DB::table('operations_resource_state_history')
            ->where('state', 'available')
            ->select('id', 'resource_id', 'started_at', 'ended_at')
            ->get()->map(fn($item) => (array) $item)->toArray();

        return [
            'summary' => [
                'total_idle_events' => count($records),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Event ID', 'Resource ID', 'Started At', 'Ended At'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $rows[] = [
                $record['id'] ?? '',
                $record['resource_id'] ?? '',
                $record['started_at'] ?? '',
                $record['ended_at'] ?? '',
            ];
        }
        return $rows;
    }
}
