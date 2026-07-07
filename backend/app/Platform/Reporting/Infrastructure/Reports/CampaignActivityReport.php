<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Modules\Campaigns\Domain\Entities\CampaignActivity;

class CampaignActivityReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'campaign_activity';
    }

    public static function getParameterSchema(): array
    {
        return [
            'campaign_id' => 'nullable|string',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $campaignId = $parameters->getString('campaign_id');

        $query = CampaignActivity::query();

        if (!empty($campaignId)) {
            $query->where('campaign_id', $campaignId);
        }

        $activities = $query->orderBy('created_at', 'desc')->take(500)->get();

        $records = $activities->map(fn(CampaignActivity $ca) => [
            'id' => $ca->id,
            'campaign_id' => $ca->campaign_id,
            'action' => $ca->action,
            'event_name' => $ca->event_name,
            'created_at' => $ca->created_at?->toIso8601String(),
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
        return ['Campaign ID', 'Action', 'Event Type', 'Timestamp'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $rec) {
            $rows[] = [
                $rec['campaign_id'],
                $rec['action'],
                $rec['event_name'],
                $rec['created_at'],
            ];
        }
        return $rows;
    }
}
