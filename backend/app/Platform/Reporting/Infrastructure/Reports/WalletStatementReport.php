<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\Wallet\Domain\Entities\WalletTransaction;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Identity\Application\Services\IdentityContext;

class WalletStatementReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'wallet_statement';
    }

    public static function getParameterSchema(): array
    {
        return [
            'wallet_id' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $walletId = $parameters->getString('wallet_id');
        $startDate = $parameters->getString('start_date');
        $endDate = $parameters->getString('end_date');

        $query = WalletTransaction::query();

        // Multi-tenant scope
        $orgId = IdentityContext::organizationId();
        if ($orgId) {
            $query->where('organization_id', $orgId);
        }

        if (!empty($walletId)) {
            $query->where('wallet_id', $walletId);
        }
        if (!empty($startDate)) {
            $query->where('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->where('created_at', '<=', $endDate);
        }

        $records = $query->orderBy('sequence_number', 'asc')->get();

        return [
            'summary' => [
                'total_transactions' => $records->count(),
                'net_amount_cents' => (int) $records->sum('amount_cents'),
            ],
            'records' => $records->map(fn(WalletTransaction $tx) => [
                'id' => $tx->id,
                'wallet_id' => $tx->wallet_id,
                'transaction_reference' => $tx->transaction_reference,
                'amount_cents' => $tx->amount_cents,
                'running_balance_snapshot' => $tx->running_balance_snapshot,
                'type' => $tx->type->value ?? $tx->type,
                'posting_status' => $tx->posting_status->value ?? $tx->posting_status,
                'reference_number' => $tx->reference_number,
                'created_at' => $tx->created_at?->toIso8601String(),
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['ID', 'Wallet ID', 'Reference', 'Amount Cents', 'Balance Snapshot', 'Type', 'Status', 'Date'];
    }

    public function getExportRows(array $data): array
    {
        return array_map(fn($r) => [
            $r['id'],
            $r['wallet_id'],
            $r['transaction_reference'],
            $r['amount_cents'],
            $r['running_balance_snapshot'],
            $r['type'],
            $r['posting_status'],
            $r['created_at'],
        ], $data['records'] ?? []);
    }
}
