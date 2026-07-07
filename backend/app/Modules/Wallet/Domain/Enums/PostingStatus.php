<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Enums;

enum PostingStatus: string
{
    case Pending = 'pending';
    case Posted = 'posted';
    case Reversed = 'reversed';
    case Failed = 'failed';
}
