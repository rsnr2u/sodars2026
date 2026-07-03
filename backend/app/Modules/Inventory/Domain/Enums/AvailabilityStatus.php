<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Enums;

enum AvailabilityStatus: string
{
    case Maintenance = 'maintenance';
    case Blocked = 'blocked';
    case Reserved = 'reserved';
    case Unavailable = 'unavailable';
    case Operational = 'operational';
}
