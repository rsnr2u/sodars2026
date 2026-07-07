<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\Wallet\Domain\Entities\Withdrawal;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Identity\Application\Services\IdentityContext;

class WithdrawalHistoryReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'withdrawal_history';
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
        $query = Withdrawal::query();

        // Multi-tenant scope
        $orgId = IdentityContext::organizationId();
        if ($orgId) {
            $query->where('organization_id', $orgId);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $records = $query->orderBy('created_at', 'desc')->get();

        return [
            'summary' => [
                'total_withdrawals' => $records->count(),
                'total_amount_cents' => (int) $records->sum('amount_cents'),
            ],
            'records' => $records->map(fn(Withdrawal $w) => [
                'id' => $w->id,
                'withdrawal_number' => $w->withdrawal_number,
                'wallet_id' => $w->wallet_id,
                'amount_cents' => $w->amount_cents,
                'bank_account_details' => $w->bank_account_details,
                'status' => $w->status->value ?? $w->status,
                'payout_reference' => $w->payout_reference,
                'created_at' => $w->created_at?->toIso8601String(),
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Withdrawal Number', 'Wallet ID', 'Amount Cents', 'Status', 'Payout Reference', 'Requested Date'];
    }

    public function getExportRows(array $data): array
    {
        return array_map(fn($r) => [
            $r['withdrawal_number'],
            $r['wallet_id'],
            $r['amount_cents'],
            $r['status'],
            $r['payout_reference'],
            $r['created_at'],
        ], $data['records'] ?? []);
    }
}
