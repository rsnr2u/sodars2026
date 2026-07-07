<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Modules\Campaigns\Domain\Entities\Campaign;

class CampaignTimelineReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'campaign_timeline';
    }

    public static function getParameterSchema(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $startDate = $parameters->getString('start_date');
        $endDate = $parameters->getString('end_date');

        $query = Campaign::query();

        if (!empty($startDate)) {
            $query->where('start_date', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->where('end_date', '<=', $endDate);
        }

        $campaigns = $query->orderBy('start_date', 'asc')->take(500)->get();

        $records = $campaigns->map(fn(Campaign $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'campaign_code' => $c->campaign_code,
            'start_date' => $c->start_date?->toDateString(),
            'end_date' => $c->end_date?->toDateString(),
            'duration_days' => $c->start_date && $c->end_date ? $c->start_date->diffInDays($c->end_date) : 0,
        ])->toArray();

        return [
            'summary' => [
                'count' => $campaigns->count(),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Campaign Name', 'Code', 'Start Date', 'End Date', 'Duration (Days)'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $rec) {
            $rows[] = [
                $rec['name'],
                $rec['campaign_code'],
                $rec['start_date'],
                $rec['end_date'],
                $rec['duration_days'],
            ];
        }
        return $rows;
    }
}
