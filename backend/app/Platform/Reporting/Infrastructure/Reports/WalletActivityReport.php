<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\Wallet\Domain\Entities\WalletActivity;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Identity\Application\Services\IdentityContext;

class WalletActivityReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'wallet_activity';
    }

    public static function getParameterSchema(): array
    {
        return [
            'wallet_id' => 'nullable|string',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $walletId = $parameters->getString('wallet_id');
        $query = WalletActivity::query();

        // Multi-tenant scope
        $orgId = IdentityContext::organizationId();
        if ($orgId) {
            $query->where('organization_id', $orgId);
        }

        if (!empty($walletId)) {
            $query->where('wallet_id', $walletId);
        }

        $records = $query->orderBy('created_at', 'desc')->get();

        return [
            'summary' => [
                'total_activities' => $records->count(),
            ],
            'records' => $records->map(fn(WalletActivity $a) => [
                'id' => $a->id,
                'wallet_id' => $a->wallet_id,
                'performed_by' => $a->performed_by,
                'action' => $a->action,
                'description' => $a->description,
                'created_at' => $a->created_at?->toIso8601String(),
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['ID', 'Wallet ID', 'Performed By', 'Action', 'Description', 'Date'];
    }

    public function getExportRows(array $data): array
    {
        return array_map(fn($r) => [
            $r['id'],
            $r['wallet_id'],
            $r['performed_by'],
            $r['action'],
            $r['description'],
            $r['created_at'],
        ], $data['records'] ?? []);
    }
}
