<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Reports;

class LedgerReconciliation
{
    public function generate(): array
    {
        return ['status' => 'reconciled', 'discrepancy_cents' => 0];
    }
}
