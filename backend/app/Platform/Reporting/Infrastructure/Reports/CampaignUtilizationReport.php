<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Modules\Campaigns\Domain\Entities\Campaign;

class CampaignUtilizationReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'campaign_utilization';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $campaigns = Campaign::with(['creatives', 'schedules'])->take(500)->get();

        $records = $campaigns->map(fn(Campaign $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'campaign_code' => $c->campaign_code,
            'creative_count' => $c->creatives->count(),
            'schedule_slots' => $c->schedules->count(),
        ])->toArray();

        return [
            'summary' => [
                'count' => $campaigns->count(),
                'total_creatives' => $campaigns->sum(fn($c) => $c->creatives->count()),
                'total_schedule_slots' => $campaigns->sum(fn($c) => $c->schedules->count()),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Campaign Name', 'Code', 'Creatives Count', 'Schedule Slots Count'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $rec) {
            $rows[] = [
                $rec['name'],
                $rec['campaign_code'],
                $rec['creative_count'],
                $rec['schedule_slots'],
            ];
        }
        return $rows;
    }
}
