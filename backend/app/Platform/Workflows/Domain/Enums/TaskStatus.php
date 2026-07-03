<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Delegated = 'delegated';
    case Escalated = 'escalated';
    case Cancelled = 'cancelled';
}
