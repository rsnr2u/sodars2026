<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Pipelines\Stages;

use App\Modules\Bookings\Domain\Enums\BookingStatus;
use Closure;
use InvalidArgumentException;

class ValidateBookingStatus
{
    public function handle(array $passable, Closure $next): mixed
    {
        $booking = $passable['booking'];

        // Proforma: booking approved or later
        // Tax Invoice: booking payment verified or later
        $invoiceType = $passable['invoice_type'];

        if ($invoiceType === 'tax_invoice') {
            $isEligible = in_array($booking->status->value ?? $booking->status, [
                BookingStatus::BranchReview->value,
                BookingStatus::ProviderReview->value,
                BookingStatus::Approved->value,
                BookingStatus::Scheduled->value,
                BookingStatus::Active->value,
                BookingStatus::Completed->value,
            ], true);

            if (!$isEligible) {
                throw new InvalidArgumentException("Booking status must be branch_review or higher to issue Tax Invoice. Current: " . ($booking->status->value ?? $booking->status));
            }
        }

        return $next($passable);
    }
}
