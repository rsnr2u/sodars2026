<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Queries;

use App\Modules\Bookings\Application\DTOs\BookingFilterData;
use App\Modules\Bookings\Domain\Repositories\BookingReadRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ListBookingsQuery
{
    public function __construct(
        protected BookingReadRepositoryInterface $readRepo
    ) {}

    /**
     * @return LengthAwarePaginator<\App\Modules\Bookings\Domain\Entities\Booking>
     */
    public function execute(BookingFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->readRepo->paginate($filters, $perPage);
    }
}
