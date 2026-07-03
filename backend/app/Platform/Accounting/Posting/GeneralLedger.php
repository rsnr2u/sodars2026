<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Posting;

use App\Platform\Accounting\ChartOfAccounts\LedgerAccount;
use App\Platform\Accounting\Journal\LedgerEntry;
use App\Platform\Accounting\Journal\EntryType;

class GeneralLedger
{
    /**
     * Compute current balance based on Account normal_balance definitions.
     */
    public function calculateBalance(LedgerAccount $account): int
    {
        $debits = (int) LedgerEntry::where('ledger_account_id', $account->id)
            ->where('entry_type', EntryType::Debit->value)
            ->sum('amount_cents');

        $credits = (int) LedgerEntry::where('ledger_account_id', $account->id)
            ->where('entry_type', EntryType::Credit->value)
            ->sum('amount_cents');

        // Assets and Expenses normal balance is Debit (DR increases).
        // Liabilities, Equity, and Revenue normal balance is Credit (CR increases).
        if ($account->normal_balance === 'debit') {
            return $debits - $credits;
        }

        return $credits - $debits;
    }
}
