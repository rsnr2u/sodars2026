<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;

class OptimizationSavingsReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'operations_optimization_savings';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        return [
            'summary' => [
                'distance_saved_km' => 124.8,
                'hours_saved' => 12.5,
            ],
            'records' => [
                [
                    'metric' => 'Distance optimization savings',
                    'value' => '124.8 km',
                ],
                [
                    'metric' => 'Workload balancing hours saved',
                    'value' => '12.5 hours',
                ]
            ],
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Metric Name', 'Metric Value'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $rows[] = [
                $record['metric'] ?? '',
                $record['value'] ?? '',
            ];
        }
        return $rows;
    }
}
