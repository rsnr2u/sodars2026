<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Enums;

enum ResourceType: string
{
    case Vehicle = 'vehicle';
    case Driver = 'driver';
    case Employee = 'employee';
    case Technician = 'technician';
    case Equipment = 'equipment';
}
