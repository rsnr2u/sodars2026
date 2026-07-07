<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Enums;

enum ConflictType: string
{
    case DoubleBooking = 'double_booking';
    case ResourceUnavailable = 'resource_unavailable';
    case QualificationExpired = 'qualification_expired';
    case RestPeriodViolation = 'rest_period_violation';
}
