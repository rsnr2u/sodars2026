<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Repositories;

use App\Modules\Bookings\Domain\Entities\Booking;

interface BookingWriteRepositoryInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Booking;

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): Booking;

    public function delete(string $id): bool;
}
