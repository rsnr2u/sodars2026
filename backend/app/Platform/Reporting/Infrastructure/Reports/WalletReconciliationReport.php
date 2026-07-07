<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Modules\Wallet\Domain\Services\WalletBalanceCalculator;
use App\Modules\Wallet\Domain\Services\WalletService;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Identity\Application\Services\IdentityContext;

class WalletReconciliationReport implements Report, Exportable
{
    protected WalletBalanceCalculator $balanceCalculator;
    protected WalletService $walletService;

    public function __construct(
        WalletBalanceCalculator $balanceCalculator,
        WalletService $walletService
    ) {
        $this->balanceCalculator = $balanceCalculator;
        $this->walletService = $walletService;
    }

    public static function getKey(): string
    {
        return 'wallet_reconciliation';
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

        $records = $wallets->map(function (Wallet $w) {
            // Authoritative transaction sum
            $dynamicSum = $this->walletService->calculateDynamicBalance($w->id);

            // Authoritative general ledger balance
            $ledgerBalance = $this->balanceCalculator->calculate($w);

            // Cached balance value
            $cachedBalance = (int) $w->balance;

            // Outstanding requested withdrawals
            $outstandingWithdrawals = (int) \App\Modules\Wallet\Domain\Entities\Withdrawal::where('wallet_id', $w->id)
                ->where('status', \App\Modules\Wallet\Domain\Enums\WithdrawalStatus::Requested->value)
                ->sum('amount_cents');

            $isReconciled = ($dynamicSum === $ledgerBalance) && ($dynamicSum === $cachedBalance);

            return [
                'wallet_id' => $w->id,
                'wallet_number' => $w->wallet_number,
                'cached_balance_cents' => $cachedBalance,
                'transaction_sum_cents' => $dynamicSum,
                'general_ledger_balance_cents' => $ledgerBalance,
                'outstanding_withdrawals_cents' => $outstandingWithdrawals,
                'is_reconciled' => $isReconciled,
                'variance_cents' => $dynamicSum - $ledgerBalance,
            ];
        });

        return [
            'summary' => [
                'total_reconciled' => $records->where('is_reconciled', true)->count(),
                'total_unreconciled' => $records->where('is_reconciled', false)->count(),
            ],
            'records' => $records->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Wallet Number', 'Cached Balance', 'Tx Sum', 'GL Balance', 'Outstanding Withdrawals', 'Reconciled', 'Variance'];
    }

    public function getExportRows(array $data): array
    {
        return array_map(fn($r) => [
            $r['wallet_number'],
            $r['cached_balance_cents'],
            $r['transaction_sum_cents'],
            $r['general_ledger_balance_cents'],
            $r['outstanding_withdrawals_cents'],
            $r['is_reconciled'] ? 'Yes' : 'No',
            $r['variance_cents'],
        ], $data['records'] ?? []);
    }
}
