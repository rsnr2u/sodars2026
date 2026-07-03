<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Enums;

enum FollowUpStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case OVERDUE = 'overdue';
}
