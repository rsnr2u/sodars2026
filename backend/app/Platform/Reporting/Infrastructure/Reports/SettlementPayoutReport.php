<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\Wallet\Domain\Entities\WalletTransaction;
use App\Modules\Wallet\Domain\Enums\TransactionType;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Identity\Application\Services\IdentityContext;

class SettlementPayoutReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'settlement_payout';
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

        $query = WalletTransaction::query()->where('type', TransactionType::Settlement->value);

        // Multi-tenant scope
        $orgId = IdentityContext::organizationId();
        if ($orgId) {
            $query->where('organization_id', $orgId);
        }

        if (!empty($startDate)) {
            $query->where('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->where('created_at', '<=', $endDate);
        }

        $records = $query->orderBy('created_at', 'desc')->get();

        return [
            'summary' => [
                'total_settlement_credits' => $records->count(),
                'total_settled_amount_cents' => (int) $records->sum('amount_cents'),
            ],
            'records' => $records->map(fn(WalletTransaction $tx) => [
                'id' => $tx->id,
                'wallet_id' => $tx->wallet_id,
                'amount_cents' => $tx->amount_cents,
                'settlement_id' => $tx->settlement_id,
                'invoice_id' => $tx->invoice_id,
                'reference_number' => $tx->reference_number,
                'transaction_reference' => $tx->transaction_reference,
                'created_at' => $tx->created_at?->toIso8601String(),
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['TX Reference', 'Wallet ID', 'Settled Amount Cents', 'Settlement ID', 'Invoice ID', 'Date'];
    }

    public function getExportRows(array $data): array
    {
        return array_map(fn($r) => [
            $r['transaction_reference'],
            $r['wallet_id'],
            $r['amount_cents'],
            $r['settlement_id'],
            $r['invoice_id'],
            $r['created_at'],
        ], $data['records'] ?? []);
    }
}
