<?php

declare(strict_types=1);

namespace App\Platform\Accounting\ChartOfAccounts;

use App\Core\Models\BaseBusinessModel;

class AccountingPeriod extends BaseBusinessModel
{
    protected $table = 'accounting_periods';

    protected $fillable = [
        'organization_id',
        'id',
        'fiscal_year',
        'month',
        'status',
    ];
}
