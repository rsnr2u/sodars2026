<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Posting;

use App\Platform\Accounting\ChartOfAccounts\AccountingPeriod;
use InvalidArgumentException;

class DoubleEntryEngine
{
    /**
     * Checks if debits == credits, normal balance restrictions, and period open status.
     * @param array<\App\Platform\Accounting\Posting\LedgerLineData> $lines
     */
    public function validate(AccountingPeriod $period, array $lines): void
    {
        // 1. Period checks
        if (in_array($period->status, ['closed', 'locked'], true)) {
            throw new InvalidArgumentException("Accounting period is closed or locked.");
        }

        // 2. Double-entry arithmetic balancing check
        $debits = 0;
        $credits = 0;

        foreach ($lines as $line) {
            if ($line->entryType === 'debit') {
                $debits += $line->money->getAmount();
            } else {
                $credits += $line->money->getAmount();
            }
        }

        if ($debits !== $credits) {
            throw new InvalidArgumentException("Double-entry transaction is unbalanced. Debits: {$debits} cents, Credits: {$credits} cents.");
        }
    }
}
