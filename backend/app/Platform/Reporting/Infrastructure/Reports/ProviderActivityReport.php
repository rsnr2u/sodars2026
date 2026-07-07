<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Modules\Providers\Domain\Entities\ProviderActivity;

class ProviderActivityReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'provider_activity';
    }

    public static function getParameterSchema(): array
    {
        return [
            'activity_type' => 'nullable|string',
            'provider_id' => 'nullable|string',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $activityType = $parameters->getString('activity_type');
        $providerId = $parameters->getString('provider_id');

        $query = ProviderActivity::query();

        if (!empty($activityType)) {
            $query->where('activity_type', $activityType);
        }
        if (!empty($providerId)) {
            $query->where('provider_id', $providerId);
        }

        $activities = $query->orderBy('created_at', 'desc')->take(500)->get();

        $records = $activities->map(fn(ProviderActivity $pa) => [
            'id' => $pa->id,
            'provider_id' => $pa->provider_id,
            'activity_type' => $pa->activity_type,
            'description' => $pa->description,
            'created_at' => $pa->created_at?->toIso8601String(),
        ])->toArray();

        return [
            'summary' => [
                'total_activities' => $activities->count(),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Provider ID', 'Activity Type', 'Description', 'Timestamp'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $rec) {
            $rows[] = [
                $rec['provider_id'],
                $rec['activity_type'],
                $rec['description'],
                $rec['created_at'],
            ];
        }
        return $rows;
    }
}
