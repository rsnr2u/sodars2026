<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Queries;

use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Repositories\BookingReadRepositoryInterface;

class GetBookingDetailsQuery
{
    public function __construct(
        protected BookingReadRepositoryInterface $readRepo
    ) {}

    public function execute(string $id): Booking
    {
        return $this->readRepo->findOrFail($id);
    }
}
