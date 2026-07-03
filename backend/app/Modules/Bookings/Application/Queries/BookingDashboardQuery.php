<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Queries;

use App\Modules\Bookings\Application\DTOs\BookingDashboardDTO;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Enums\BookingStatus;

class BookingDashboardQuery
{
    public function execute(?string $customerId = null): BookingDashboardDTO
    {
        $query = Booking::query();

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        $totalBookings = $query->count();
        $pendingBookings = (clone $query)->whereIn('status', [
            BookingStatus::Submitted->value,
            BookingStatus::BranchReview->value,
            BookingStatus::ProviderReview->value,
        ])->count();

        $approvedBookings = (clone $query)->where('status', BookingStatus::Approved->value)->count();
        $activeBookings = (clone $query)->where('status', BookingStatus::Active->value)->count();

        $totalRevenueCents = (int) $query->whereNotIn('status', [
            BookingStatus::Cancelled->value,
            BookingStatus::Rejected->value,
            BookingStatus::Draft->value,
        ])->sum('grand_total_cents');

        return new BookingDashboardDTO(
            totalBookings: $totalBookings,
            pendingBookings: $pendingBookings,
            approvedBookings: $approvedBookings,
            activeBookings: $activeBookings,
            totalRevenueCents: $totalRevenueCents
        );
    }
}
