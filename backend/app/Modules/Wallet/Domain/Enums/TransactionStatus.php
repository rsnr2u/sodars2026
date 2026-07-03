<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Enums;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
}
