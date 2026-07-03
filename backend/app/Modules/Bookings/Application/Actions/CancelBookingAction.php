<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Actions;

use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Repositories\BookingReadRepositoryInterface;
use App\Modules\Bookings\Domain\Services\BookingLifecycleService;
use App\Modules\Bookings\Domain\Enums\BookingStatus;

class CancelBookingAction
{
    public function __construct(
        protected BookingReadRepositoryInterface $bookingReadRepo,
        protected BookingLifecycleService $lifecycleService
    ) {}

    public function execute(string $bookingId, ?string $comment = null): Booking
    {
        $booking = $this->bookingReadRepo->findOrFail($bookingId);
        
        return $this->lifecycleService->transition($booking, BookingStatus::Cancelled->value, $comment);
    }
}
