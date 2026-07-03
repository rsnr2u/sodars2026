<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Infrastructure\Repositories;

use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Repositories\BookingWriteRepositoryInterface;

class BookingWriteRepository implements BookingWriteRepositoryInterface
{
    public function create(array $data): Booking
    {
        return Booking::create($data);
    }

    public function update(string $id, array $data): Booking
    {
        $booking = Booking::findOrFail($id);
        $booking->update($data);
        return $booking;
    }

    public function delete(string $id): bool
    {
        $booking = Booking::findOrFail($id);
        return $booking->delete();
    }
}
