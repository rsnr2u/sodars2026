<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Enums;

enum VehicleStatus: string
{
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';
}
