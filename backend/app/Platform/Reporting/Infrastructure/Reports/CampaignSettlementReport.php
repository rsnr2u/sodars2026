<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Modules\Campaigns\Domain\Entities\Campaign;

class CampaignSettlementReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'campaign_settlement';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $campaigns = Campaign::take(500)->get();

        $totalCents = 0;
        $records = [];

        foreach ($campaigns as $c) {
            // Allocate 80% as provider settlement payout share by default for illustration
            $providerShare = (int) (($c->budget_cents ?? 0) * 0.8);
            $commission = (int) (($c->budget_cents ?? 0) * 0.15);
            $tax = (int) (($c->budget_cents ?? 0) * 0.05);

            $totalCents += $c->budget_cents ?? 0;

            $records[] = [
                'campaign_id' => $c->id,
                'campaign_code' => $c->campaign_code,
                'name' => $c->name,
                'total_budget_cents' => $c->budget_cents ?? 0,
                'provider_share_cents' => $providerShare,
                'commission_cents' => $commission,
                'tax_cents' => $tax,
            ];
        }

        return [
            'summary' => [
                'total_budget_cents' => $totalCents,
                'total_provider_share_cents' => (int) ($totalCents * 0.8),
                'total_commission_cents' => (int) ($totalCents * 0.15),
                'total_tax_cents' => (int) ($totalCents * 0.05),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Campaign Code', 'Campaign Name', 'Budget Cents', 'Provider Share Cents', 'Commission Cents', 'Tax Cents'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $rec) {
            $rows[] = [
                $rec['campaign_code'],
                $rec['name'],
                $rec['total_budget_cents'],
                $rec['provider_share_cents'],
                $rec['commission_cents'],
                $rec['tax_cents'],
            ];
        }
        return $rows;
    }
}
