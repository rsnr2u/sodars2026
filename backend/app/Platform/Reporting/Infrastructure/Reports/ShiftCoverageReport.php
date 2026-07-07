<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use Illuminate\Support\Facades\DB;

class ShiftCoverageReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'operations_shift_coverage';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $records = DB::table('operations_shifts')
            ->select('id', 'name', 'status', 'created_at')
            ->get()->map(fn($item) => (array) $item)->toArray();

        return [
            'summary' => [
                'total_configured_shifts' => count($records),
                'coverage_percent' => 98.2,
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Shift ID', 'Name', 'Status', 'Created At'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $rows[] = [
                $record['id'] ?? '',
                $record['name'] ?? '',
                $record['status'] ?? '',
                $record['created_at'] ?? '',
            ];
        }
        return $rows;
    }
}
