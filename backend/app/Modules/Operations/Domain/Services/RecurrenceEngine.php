<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Services;

use App\Modules\Operations\Domain\ValueObjects\RecurrencePattern;
use Carbon\Carbon;

class RecurrenceEngine
{
    /**
     * Generate future occurrence date ranges from a recurrence pattern.
     *
     * @return array<int, array{start: Carbon, end: Carbon}>
     */
    public function generateOccurrences(
        Carbon $baseStart,
        Carbon $baseEnd,
        RecurrencePattern $pattern,
        array $holidays = []
    ): array {
        $occurrences = [];
        $currentStart = $baseStart->copy();
        $currentEnd = $baseEnd->copy();

        $limit = 50; // Safety limit
        $count = 0;

        $endsAt = $pattern->endsAt ? Carbon::parse($pattern->endsAt) : now()->addMonths(3);

        while ($currentStart->lessThanOrEqualTo($endsAt) && $count < $limit) {
            // Check exception dates
            $dateString = $currentStart->toDateString();
            if (in_array($dateString, $pattern->exceptionDates, true)) {
                $currentStart = $this->incrementDate($currentStart, $pattern);
                $currentEnd = $this->incrementDate($currentEnd, $pattern);
                continue;
            }

            // Check holiday skipping
            if (in_array($dateString, $holidays, true)) {
                $currentStart = $this->incrementDate($currentStart, $pattern);
                $currentEnd = $this->incrementDate($currentEnd, $pattern);
                continue;
            }

            // Check if specific days match weekly parameters
            if ($pattern->frequency === 'weekly' && !empty($pattern->byDays)) {
                $dayName = strtolower($currentStart->format('l')); // monday, tuesday, etc.
                if (!in_array($dayName, $pattern->byDays, true)) {
                    $currentStart = $currentStart->addDay();
                    $currentEnd = $currentEnd->addDay();
                    continue;
                }
            }

            $occurrences[] = [
                'start' => $currentStart->copy(),
                'end' => $currentEnd->copy(),
            ];

            $currentStart = $this->incrementDate($currentStart, $pattern);
            $currentEnd = $this->incrementDate($currentEnd, $pattern);
            $count++;
        }

        return $occurrences;
    }

    protected function incrementDate(Carbon $date, RecurrencePattern $pattern): Carbon
    {
        return match ($pattern->frequency) {
            'daily' => $date->addDays($pattern->interval),
            'weekly' => $date->addWeeks($pattern->interval),
            'monthly' => $date->addMonths($pattern->interval),
            default => $date->addDay(),
        };
    }
}
