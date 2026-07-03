<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Enums;

enum ContactType: string
{
    case Owner = 'owner';
    case Accounts = 'accounts';
    case Operations = 'operations';
    case Sales = 'sales';
    case Emergency = 'emergency';
}
