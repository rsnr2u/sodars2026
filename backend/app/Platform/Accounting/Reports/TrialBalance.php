<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Reports;

use App\Platform\Accounting\Journal\LedgerEntry;
use App\Platform\Accounting\Journal\EntryType;

class TrialBalance
{
    public function generate(): array
    {
        $debits = (int) LedgerEntry::where('entry_type', EntryType::Debit->value)->sum('amount_cents');
        $credits = (int) LedgerEntry::where('entry_type', EntryType::Credit->value)->sum('amount_cents');

        return [
            'total_debit_cents' => $debits,
            'total_credit_cents' => $credits,
            'is_balanced' => $debits === $credits,
        ];
    }
}
