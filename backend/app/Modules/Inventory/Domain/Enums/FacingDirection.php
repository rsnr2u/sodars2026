<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Enums;

enum FacingDirection: string
{
    case North = 'north';
    case South = 'south';
    case East = 'east';
    case West = 'west';
    case NorthEast = 'north_east';
    case NorthWest = 'north_west';
    case SouthEast = 'south_east';
    case SouthWest = 'south_west';
}
