<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Enums;

enum FaceStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Maintenance = 'maintenance';
}
