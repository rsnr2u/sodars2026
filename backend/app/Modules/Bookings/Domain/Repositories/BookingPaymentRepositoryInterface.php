<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Repositories;

use App\Modules\Finance\Domain\Entities\Payment;

interface BookingPaymentRepositoryInterface
{
    public function findById(string $id): ?Payment;

    public function findOrFail(string $id): Payment;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Payment;

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): Payment;
}
