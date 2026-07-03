<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Reports;

class GeneralLedgerReport
{
    public function generate(): array
    {
        return ['status' => 'success', 'data' => []];
    }
}
