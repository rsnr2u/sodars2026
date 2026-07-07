<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Enums;

enum ScheduleStatus: string
{
    case Draft = 'draft';
    case Planned = 'planned';
    case Validated = 'validated';
    case Optimized = 'optimized';
    case Assigned = 'assigned';
    case Approved = 'approved';
    case Dispatched = 'dispatched';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Archived = 'archived';
    case Cancelled = 'cancelled';
    case Delayed = 'delayed';
    case Suspended = 'suspended';
    case Failed = 'failed';
}
