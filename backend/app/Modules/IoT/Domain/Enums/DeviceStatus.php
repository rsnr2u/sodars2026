<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Enums;

enum DeviceStatus: string
{
    case Active = 'Active';
    case Offline = 'Offline';
    case Maintenance = 'Maintenance';
    case Error = 'Error';
}
