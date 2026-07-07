<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Enums;

enum RouteStatus: string
{
    case Draft = 'Draft';
    case Planned = 'Planned';
    case Assigned = 'Assigned';
    case Dispatched = 'Dispatched';
    case InTransit = 'In Transit';
    case Arrived = 'Arrived';
    case Completed = 'Completed';
    case Archived = 'Archived';
    case Cancelled = 'Cancelled';
    case Paused = 'Paused';
    case Delayed = 'Delayed';
}
