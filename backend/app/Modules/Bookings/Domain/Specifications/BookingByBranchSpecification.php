<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Bookings\Domain\Entities\Booking;
use Illuminate\Database\Eloquent\Builder;

class BookingByBranchSpecification implements SpecificationInterface
{
    public function __construct(
        protected string $branchId
    ) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $candidate instanceof Booking && $candidate->branch_id === $this->branchId;
    }

    public function toQuery(Builder $builder): Builder
    {
        return $builder->where('branch_id', $this->branchId);
    }
}
