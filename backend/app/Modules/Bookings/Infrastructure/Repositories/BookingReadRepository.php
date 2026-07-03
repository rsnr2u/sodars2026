<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Infrastructure\Repositories;

use App\Modules\Bookings\Application\DTOs\BookingFilterData;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Repositories\BookingReadRepositoryInterface;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BookingReadRepository implements BookingReadRepositoryInterface
{
    public function findById(string $id): ?Booking
    {
        return Booking::find($id);
    }

    public function findOrFail(string $id): Booking
    {
        return Booking::findOrFail($id);
    }

    public function findByCode(string $code): ?Booking
    {
        return Booking::where('booking_code', $code)->first();
    }

    /**
     * @return LengthAwarePaginator<Booking>
     */
    public function paginate(BookingFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::query()->with(['customer', 'branch']);

        if ($filters->status) {
            $query->where('status', $filters->status);
        }

        if ($filters->customerId) {
            $query->where('customer_id', $filters->customerId);
        }

        if ($filters->branchId) {
            $query->where('branch_id', $filters->branchId);
        }

        if ($filters->startDate) {
            $query->where('start_date', '>=', $filters->startDate);
        }

        if ($filters->endDate) {
            $query->where('end_date', '<=', $filters->endDate);
        }

        if ($filters->search) {
            $query->where(function ($q) use ($filters) {
                $q->where('booking_code', 'like', '%' . $filters->search . '%');
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getActiveBookings(): Collection
    {
        return Booking::whereIn('status', [
            BookingStatus::Approved->value,
            BookingStatus::Scheduled->value,
            BookingStatus::Active->value,
        ])->get();
    }
}
