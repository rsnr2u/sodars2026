<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Infrastructure\Repositories;

use App\Modules\Finance\Domain\Entities\Payment;
use App\Modules\Bookings\Domain\Repositories\BookingPaymentRepositoryInterface;

class BookingPaymentRepository implements BookingPaymentRepositoryInterface
{
    public function findById(string $id): ?Payment
    {
        return Payment::find($id);
    }

    public function findOrFail(string $id): Payment
    {
        return Payment::findOrFail($id);
    }

    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function update(string $id, array $data): Payment
    {
        $payment = Payment::findOrFail($id);
        $payment->update($data);
        return $payment;
    }
}
