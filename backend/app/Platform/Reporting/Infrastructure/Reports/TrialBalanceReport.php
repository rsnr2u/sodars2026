<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Accounting\Journal\LedgerEntry;
use App\Platform\Accounting\Journal\EntryType;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;

class TrialBalanceReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'trial_balance';
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

        $query = LedgerEntry::query();

        if (!empty($startDate)) {
            $query->where('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->where('created_at', '<=', $endDate);
        }

        $entries = $query->get();

        $debits = $entries->where('entry_type', EntryType::Debit->value)->sum('amount_cents');
        $credits = $entries->where('entry_type', EntryType::Credit->value)->sum('amount_cents');

        return [
            'summary' => [
                'total_debit_cents' => (int) $debits,
                'total_credit_cents' => (int) $credits,
                'is_balanced' => $debits === $credits,
            ],
            'records' => $entries->map(fn(LedgerEntry $e) => [
                'id' => $e->id,
                'account_id' => $e->account_id,
                'amount_cents' => $e->amount_cents,
                'entry_type' => $e->entry_type,
                'created_at' => $e->created_at?->toIso8601String(),
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Entry ID', 'Account ID', 'Debit Cents', 'Credit Cents', 'Timestamp'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $record) {
            $isDebit = $record['entry_type'] === EntryType::Debit->value;
            $rows[] = [
                $record['id'],
                $record['account_id'],
                $isDebit ? $record['amount_cents'] : 0,
                $isDebit ? 0 : $record['amount_cents'],
                $record['created_at'],
            ];
        }
        return $rows;
    }
}
