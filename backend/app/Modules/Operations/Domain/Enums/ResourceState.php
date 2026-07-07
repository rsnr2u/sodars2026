<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Enums;

enum ResourceState: string
{
    case Available = 'available';
    case Assigned = 'assigned';
    case Traveling = 'traveling';
    case Waiting = 'waiting';
    case Offline = 'offline';
    case Maintenance = 'maintenance';
    case Break = 'break';
    case Leave = 'leave';
}
