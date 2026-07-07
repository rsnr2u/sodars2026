<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Enums;

enum DriverStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Inactive = 'inactive';
}
