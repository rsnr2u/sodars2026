<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Modules\Campaigns\Domain\Entities\Campaign;

class CampaignBudgetVarianceReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'campaign_budget_variance';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $campaigns = Campaign::take(500)->get();

        $totalPlanned = $campaigns->sum('planned_budget_cents');
        $totalApproved = $campaigns->sum('approved_budget_cents');
        $totalActual = $campaigns->sum('actual_spend_cents');
        $totalRemaining = $campaigns->sum('remaining_budget_cents');

        $records = $campaigns->map(fn(Campaign $c) => [
            'id' => $c->id,
            'campaign_code' => $c->campaign_code,
            'name' => $c->name,
            'planned_budget_cents' => $c->planned_budget_cents,
            'approved_budget_cents' => $c->approved_budget_cents,
            'actual_spend_cents' => $c->actual_spend_cents,
            'remaining_budget_cents' => $c->remaining_budget_cents,
            'variance_cents' => $c->approved_budget_cents - $c->actual_spend_cents,
        ])->toArray();

        return [
            'summary' => [
                'total_planned_budget_cents' => (int) $totalPlanned,
                'total_approved_budget_cents' => (int) $totalApproved,
                'total_actual_spend_cents' => (int) $totalActual,
                'total_remaining_budget_cents' => (int) $totalRemaining,
                'total_variance_cents' => (int) ($totalApproved - $totalActual),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Campaign Code', 'Campaign Name', 'Planned Budget', 'Approved Budget', 'Actual Spend', 'Remaining Budget', 'Variance'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $rec) {
            $rows[] = [
                $rec['campaign_code'],
                $rec['name'],
                $rec['planned_budget_cents'],
                $rec['approved_budget_cents'],
                $rec['actual_spend_cents'],
                $rec['remaining_budget_cents'],
                $rec['variance_cents'],
            ];
        }
        return $rows;
    }
}
