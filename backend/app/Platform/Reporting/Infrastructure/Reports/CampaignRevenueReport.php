<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Modules\Campaigns\Domain\Entities\Campaign;

class CampaignRevenueReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'campaign_revenue';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $campaigns = Campaign::take(500)->get();

        $totalRevenue = $campaigns->sum('budget_cents');

        $records = $campaigns->map(fn(Campaign $c) => [
            'campaign_id' => $c->id,
            'campaign_code' => $c->campaign_code,
            'name' => $c->name,
            'budget_cents' => $c->budget_cents ?? 0,
            'revenue_cents' => $c->budget_cents ?? 0, // Invoiced budget matches revenue
        ])->toArray();

        return [
            'summary' => [
                'total_revenue_cents' => (int) $totalRevenue,
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Campaign Code', 'Campaign Name', 'Revenue Cents'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $rec) {
            $rows[] = [
                $rec['campaign_code'],
                $rec['name'],
                $rec['revenue_cents'],
            ];
        }
        return $rows;
    }
}
