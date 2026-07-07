<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Identity\Application\Services\IdentityContext;
use Carbon\Carbon;

class WalletAgingReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'wallet_aging';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $query = Wallet::query();

        // Multi-tenant scope
        $orgId = IdentityContext::organizationId();
        if ($orgId) {
            $query->where('organization_id', $orgId);
        }

        $wallets = $query->with('transactions')->get();

        $records = $wallets->map(function (Wallet $w) {
            $lastTx = $w->transactions->sortByDesc('created_at')->first();
            $daysSinceLastTx = $lastTx ? (int) Carbon::parse($lastTx->created_at)->diffInDays(now()) : 9999;

            $statusCategory = 'active';
            if ($w->balance < 0) {
                $statusCategory = 'negative';
            } elseif ($w->status === \App\Modules\Wallet\Domain\Enums\WalletState::Frozen) {
                $statusCategory = 'frozen';
            } elseif ($daysSinceLastTx > 90) {
                $statusCategory = 'dormant';
            }

            return [
                'wallet_number' => $w->wallet_number,
                'holder_type' => $w->holder_type,
                'wallet_type' => $w->wallet_type,
                'balance_cents' => (int) $w->balance,
                'status' => $w->status->value ?? $w->status,
                'last_transaction_date' => $lastTx ? $lastTx->created_at?->toIso8601String() : null,
                'days_inactive' => $daysSinceLastTx,
                'aging_category' => $statusCategory,
            ];
        });

        return [
            'summary' => [
                'total_negative_balance_wallets' => $records->where('aging_category', 'negative')->count(),
                'total_dormant_wallets' => $records->where('aging_category', 'dormant')->count(),
                'total_frozen_wallets' => $records->where('aging_category', 'frozen')->count(),
            ],
            'records' => $records->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Wallet Number', 'Holder', 'Type', 'Balance Cents', 'Status', 'Last Tx Date', 'Days Inactive', 'Category'];
    }

    public function getExportRows(array $data): array
    {
        return array_map(fn($r) => [
            $r['wallet_number'],
            $r['holder_type'],
            $r['wallet_type'],
            $r['balance_cents'],
            $r['status'],
            $r['last_transaction_date'],
            $r['days_inactive'],
            $r['aging_category'],
        ], $data['records'] ?? []);
    }
}
