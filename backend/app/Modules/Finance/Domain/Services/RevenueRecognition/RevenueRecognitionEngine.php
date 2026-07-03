<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Services\RevenueRecognition;

use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Finance\Domain\Entities\RevenueRecognitionSchedule;
use App\Modules\Finance\Domain\Entities\RevenueRecognitionEntry;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;

class RevenueRecognitionEngine
{
    /**
     * Build deferred schedules linearly for booking items.
     * Generates a recognition date entry for each day of the flight.
     */
    public function generateSchedules(Booking $booking): array
    {
        $createdSchedules = [];

        foreach ($booking->items as $item) {
            $pricing = $item->pricing_snapshot;
            $net = $pricing['unit_rate'] ?? $item->net_price_cents;
            $markup = $pricing['markup'] ?? 0;
            $retail = $net + $markup;
            $totalItemPrice = $retail * ($item->daily_frequency ?? 1);

            $startDate = $item->start_date;
            $endDate = $item->end_date;
            $days = $startDate->diffInDays($endDate) + 1;

            if ($days <= 0) {
                continue;
            }

            // Divide amount linearly per day
            $dailyAmount = (int) floor($totalItemPrice / $days);
            $remainder = $totalItemPrice % $days;

            $period = CarbonPeriod::create($startDate, $endDate);
            $index = 0;

            foreach ($period as $date) {
                $amount = $dailyAmount;
                if ($index === 0) {
                    $amount += $remainder; // add rounding pennies to first day
                }

                $createdSchedules[] = RevenueRecognitionSchedule::create([
                    'id' => (string) Str::uuid(),
                    'booking_id' => $booking->id,
                    'booking_item_id' => $item->id,
                    'recognition_date' => $date->toDateString(),
                    'amount_cents' => $amount,
                    'status' => 'pending',
                ]);

                $index++;
            }
        }

        return $createdSchedules;
    }

    /**
     * Executes recognition for all schedules on/before the target date, posting entries.
     */
    public function recognizePending(string $asOfDate): array
    {
        $schedules = RevenueRecognitionSchedule::where('status', 'pending')
            ->where('recognition_date', '<=', $asOfDate)
            ->get();

        $entries = [];

        foreach ($schedules as $schedule) {
            $entries[] = RevenueRecognitionEntry::create([
                'id' => (string) Str::uuid(),
                'schedule_id' => $schedule->id,
                'recognition_date' => $schedule->recognition_date,
                'amount_cents' => $schedule->amount_cents,
                'status' => 'recognized',
            ]);

            $schedule->update(['status' => 'completed']);
        }

        return $entries;
    }
}
