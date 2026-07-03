<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Enums;

enum PayoutMethod: string
{
    case Bank = 'bank';
    case Upi = 'upi';
    case Wallet = 'wallet';
}
