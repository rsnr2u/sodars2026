<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Failed = 'failed';
}
