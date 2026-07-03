<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Reports;

class CashFlow
{
    public function generate(): array
    {
        return ['operating' => 0, 'investing' => 0, 'financing' => 0];
    }
}
