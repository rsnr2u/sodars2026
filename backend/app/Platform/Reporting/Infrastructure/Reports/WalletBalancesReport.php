<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Identity\Application\Services\IdentityContext;

class WalletBalancesReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'wallet_balances';
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

        $wallets = $query->get();

        return [
            'summary' => [
                'total_wallets' => $wallets->count(),
                'active_wallets' => $wallets->where('status', \App\Modules\Wallet\Domain\Enums\WalletState::Active)->count(),
                'total_cached_balance_cents' => (int) $wallets->sum('balance'),
            ],
            'records' => $wallets->map(fn(Wallet $w) => [
                'id' => $w->id,
                'wallet_number' => $w->wallet_number,
                'holder_type' => $w->holder_type,
                'holder_id' => $w->holder_id,
                'wallet_type' => $w->wallet_type,
                'currency' => $w->currency,
                'status' => $w->status->value ?? $w->status,
                'cached_balance_cents' => (int) $w->balance,
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Wallet Number', 'Holder Type', 'Holder ID', 'Type', 'Currency', 'Status', 'Balance Cents'];
    }

    public function getExportRows(array $data): array
    {
        return array_map(fn($r) => [
            $r['wallet_number'],
            $r['holder_type'],
            $r['holder_id'],
            $r['wallet_type'],
            $r['currency'],
            $r['status'],
            $r['cached_balance_cents'],
        ], $data['records'] ?? []);
    }
}
