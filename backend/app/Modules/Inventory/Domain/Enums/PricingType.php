<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Enums;

enum PricingType: string
{
    case Baseline = 'baseline';
    case Seasonal = 'seasonal';
    case Holiday = 'holiday';
    case Event = 'event';
}
