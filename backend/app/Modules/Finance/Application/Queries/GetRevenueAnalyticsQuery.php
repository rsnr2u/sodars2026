<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Queries;

use App\Modules\Finance\Domain\Entities\RevenueRecognitionEntry;
use App\Modules\Finance\Domain\Entities\RevenueRecognitionSchedule;

class GetRevenueAnalyticsQuery
{
    /**
     * Compute totals of earned revenue (entries recognized) vs deferred revenue (schedules pending).
     */
    public function execute(?string $bookingId = null): array
    {
        $scheduleQuery = RevenueRecognitionSchedule::query();
        $entryQuery = RevenueRecognitionEntry::query();

        if ($bookingId) {
            $scheduleQuery->where('booking_id', $bookingId);
            $entryQuery->whereHas('schedule', function ($q) use ($bookingId) {
                $q->where('booking_id', $bookingId);
            });
        }

        $deferredCents = (int) $scheduleQuery->where('status', 'pending')->sum('amount_cents');
        $earnedCents = (int) $entryQuery->where('status', 'recognized')->sum('amount_cents');

        return [
            'deferred_revenue_cents' => $deferredCents,
            'earned_revenue_cents' => $earnedCents,
            'total_revenue_cents' => $deferredCents + $earnedCents,
        ];
    }
}
