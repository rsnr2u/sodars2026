<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Enums;

enum SettlementStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
}
