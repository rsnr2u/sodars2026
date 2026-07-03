<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Reports;

class BalanceSheet
{
    public function generate(): array
    {
        return ['assets' => 0, 'liabilities' => 0, 'equity' => 0];
    }
}
