<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Enums;

enum CreativeStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
