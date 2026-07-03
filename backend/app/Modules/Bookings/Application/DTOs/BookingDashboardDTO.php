<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\DTOs;

class BookingDashboardDTO
{
    public function __construct(
        public readonly int $totalBookings,
        public readonly int $pendingBookings,
        public readonly int $approvedBookings,
        public readonly int $activeBookings,
        public readonly int $totalRevenueCents,
        public readonly string $currency = 'INR'
    ) {}

    /**
     * @return array{total_bookings: int, pending_bookings: int, approved_bookings: int, active_bookings: int, total_revenue_cents: int, currency: string}
     */
    public function toArray(): array
    {
        return [
            'total_bookings' => $this->totalBookings,
            'pending_bookings' => $this->pendingBookings,
            'approved_bookings' => $this->approvedBookings,
            'active_bookings' => $this->activeBookings,
            'total_revenue_cents' => $this->totalRevenueCents,
            'currency' => $this->currency,
        ];
    }
}
