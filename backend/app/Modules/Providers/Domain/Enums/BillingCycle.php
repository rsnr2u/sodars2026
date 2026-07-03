<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Enums;

enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';
}
