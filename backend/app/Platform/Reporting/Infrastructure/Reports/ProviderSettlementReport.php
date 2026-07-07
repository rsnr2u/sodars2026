<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Modules\Finance\Domain\Entities\ProviderSettlement;

class ProviderSettlementReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'provider_settlement';
    }

    public static function getParameterSchema(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|string',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $startDate = $parameters->getString('start_date');
        $endDate = $parameters->getString('end_date');
        $status = $parameters->getString('status');

        $query = ProviderSettlement::query();

        if (!empty($startDate)) {
            $query->where('period_start', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->where('period_end', '<=', $endDate);
        }
        if (!empty($status)) {
            $query->where('status', $status);
        }

        $settlements = $query->take(500)->get();

        $totalSettledCents = $settlements->sum('total_amount_cents');
        $totalProviderShareCents = $settlements->sum('provider_share_cents');
        $totalCommissionCents = $settlements->sum('commission_cents');
        $totalTaxCents = $settlements->sum('tax_cents');

        $records = $settlements->map(fn(ProviderSettlement $ps) => [
            'id' => $ps->id,
            'provider_id' => $ps->provider_id,
            'settlement_number' => $ps->settlement_number ?? 'SETTLE-' . substr($ps->id, 0, 8),
            'period_start' => $ps->period_start?->toDateString(),
            'period_end' => $ps->period_end?->toDateString(),
            'total_settled_cents' => $ps->total_amount_cents,
            'provider_share_cents' => $ps->provider_share_cents,
            'commission_cents' => $ps->commission_cents,
            'tax_cents' => $ps->tax_cents,
            'status' => $ps->status instanceof \BackedEnum ? $ps->status->value : (string) $ps->status,
        ])->toArray();

        return [
            'summary' => [
                'total_settled_cents' => (int) $totalSettledCents,
                'total_provider_share_cents' => (int) $totalProviderShareCents,
                'total_commission_cents' => (int) $totalCommissionCents,
                'total_tax_cents' => (int) $totalTaxCents,
                'count' => $settlements->count(),
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Settlement Number', 'Provider ID', 'Period Start', 'Period End', 'Total Settled', 'Provider Share', 'Commission', 'Tax', 'Status'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $rec) {
            $rows[] = [
                $rec['settlement_number'],
                $rec['provider_id'],
                $rec['period_start'],
                $rec['period_end'],
                $rec['total_settled_cents'],
                $rec['provider_share_cents'],
                $rec['commission_cents'],
                $rec['tax_cents'],
                $rec['status'],
            ];
        }
        return $rows;
    }
}
