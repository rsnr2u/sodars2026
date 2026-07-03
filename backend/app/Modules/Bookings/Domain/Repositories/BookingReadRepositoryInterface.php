<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Repositories;

use App\Modules\Bookings\Application\DTOs\BookingFilterData;
use App\Modules\Bookings\Domain\Entities\Booking;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface BookingReadRepositoryInterface
{
    public function findById(string $id): ?Booking;

    public function findOrFail(string $id): Booking;

    public function findByCode(string $code): ?Booking;

    /**
     * @return LengthAwarePaginator<Booking>
     */
    public function paginate(BookingFilterData $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * @return Collection<int, Booking>
     */
    public function getActiveBookings(): Collection;
}
