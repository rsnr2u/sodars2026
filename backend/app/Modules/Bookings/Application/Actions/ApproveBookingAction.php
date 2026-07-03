<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Actions;

use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Repositories\BookingReadRepositoryInterface;
use App\Modules\Bookings\Domain\Services\BookingLifecycleService;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;

class ApproveBookingAction
{
    public function __construct(
        protected BookingReadRepositoryInterface $bookingReadRepo,
        protected BookingLifecycleService $lifecycleService
    ) {}

    public function execute(string $bookingId, ?string $comment = null): Booking
    {
        $booking = $this->bookingReadRepo->findOrFail($bookingId);
        
        $approved = $this->lifecycleService->transition($booking, BookingStatus::Approved->value, $comment);

        // When booking is approved, transition campaign to scheduled if a linked campaign exists
        $campaign = Campaign::where('booking_id', $bookingId)->first();
        if ($campaign && $campaign->status === CampaignStatus::Draft) {
            $campaign->update(['status' => CampaignStatus::ArtworkPending->value]);
        }

        return $approved;
    }
}
