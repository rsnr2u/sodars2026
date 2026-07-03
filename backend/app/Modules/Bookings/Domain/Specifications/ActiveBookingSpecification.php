<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;

class ActiveBookingSpecification implements SpecificationInterface
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $candidate instanceof Booking && in_array($candidate->status, [
            BookingStatus::Approved,
            BookingStatus::Scheduled,
            BookingStatus::Active,
        ], true);
    }

    public function toQuery(Builder $builder): Builder
    {
        return $builder->whereIn('status', [
            BookingStatus::Approved->value,
            BookingStatus::Scheduled->value,
            BookingStatus::Active->value,
        ]);
    }
}
