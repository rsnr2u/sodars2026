<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Bookings\Domain\Entities\Booking;
use Illuminate\Database\Eloquent\Builder;

class BookingByCustomerSpecification implements SpecificationInterface
{
    public function __construct(
        protected string $customerId
    ) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $candidate instanceof Booking && $candidate->customer_id === $this->customerId;
    }

    public function toQuery(Builder $builder): Builder
    {
        return $builder->where('customer_id', $this->customerId);
    }
}
