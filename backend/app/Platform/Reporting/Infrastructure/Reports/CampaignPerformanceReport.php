<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Modules\Campaigns\Domain\Entities\Campaign;

class CampaignPerformanceReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'campaign_performance';
    }

    public static function getParameterSchema(): array
    {
        return [
            'status' => 'nullable|string',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $status = $parameters->getString('status');

        $query = Campaign::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $campaigns = $query->take(500)->get();

        $totalBudget = $campaigns->sum('budget_cents');

        $records = $campaigns->map(fn(Campaign $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'campaign_code' => $c->campaign_code,
            'status' => $c->status instanceof \BackedEnum ? $c->status->value : (string) $c->status,
            'budget_cents' => $c->budget_cents ?? 0,
            'start_date' => $c->start_date?->toDateString(),
            'end_date' => $c->end_date?->toDateString(),
        ])->toArray();

        return [
            'summary' => [
                'total_campaigns' => $campaigns->count(),
                'total_budget_cents' => (int) $totalBudget,
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Campaign Name', 'Code', 'Status', 'Budget', 'Start Date', 'End Date'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $rec) {
            $rows[] = [
                $rec['name'],
                $rec['campaign_code'],
                $rec['status'],
                $rec['budget_cents'],
                $rec['start_date'],
                $rec['end_date'],
            ];
        }
        return $rows;
    }
}
