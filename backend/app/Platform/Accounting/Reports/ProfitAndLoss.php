<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Reports;

class ProfitAndLoss
{
    public function generate(): array
    {
        return ['revenue' => 0, 'expenses' => 0, 'net_profit' => 0];
    }
}
