<?php

declare(strict_types=1);

namespace App\Platform\Accounting\ChartOfAccounts;

use App\Core\Models\BaseModel;

class AccountingPeriod extends BaseModel
{
    protected $table = 'accounting_periods';

    protected $fillable = [
        'id',
        'fiscal_year',
        'month',
        'status',
    ];
}
