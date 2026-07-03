<?php

declare(strict_types=1);

namespace App\Platform\Scheduling;

use Carbon\CarbonImmutable;

/**
 * Service for calendar date segment slicing and grid generation.
 *
 * Splits date ranges into daily, weekly, or monthly calendar blocks.
 */
final class CalendarService
{
    public function __construct(
        protected ?HolidayCalendar $holidayCalendar = null,
        protected ?WorkingHours $workingHours = null
    ) {}

    /**
     * Split a DateRange into daily segments.
     *
     * @return array<int, DateRange>
     */
    public function toDailySegments(DateRange $range): array
    {
        return $range->toDailySegments();
    }

    /**
     * Split a DateRange into ISO week segments.
     *
     * @return array<int, DateRange>
     */
    public function toWeeklySegments(DateRange $range): array
    {
        $segments = [];
        $cursor = $range->start->startOfWeek();
        $limit = $range->end;

        while ($cursor->lte($limit)) {
            $weekEnd = $cursor->endOfWeek();
            $segStart = $cursor->max($range->start);
            $segEnd = $weekEnd->min($range->end);

            $segments[] = new DateRange($segStart, $segEnd);
            $cursor = $weekEnd->addDay()->startOfDay();
        }

        return $segments;
    }

    /**
     * Split a DateRange into monthly segments.
     *
     * @return array<int, DateRange>
     */
    public function toMonthlySegments(DateRange $range): array
    {
        $segments = [];
        $cursor = $range->start->startOfMonth();
        $limit = $range->end;

        while ($cursor->lte($limit)) {
            $monthEnd = $cursor->endOfMonth();
            $segStart = $cursor->max($range->start);
            $segEnd = $monthEnd->min($range->end);

            $segments[] = new DateRange($segStart, $segEnd);
            $cursor = $monthEnd->addDay()->startOfDay();
        }

        return $segments;
    }

    /**
     * Count effective working days in a range (excluding holidays and non-working days).
     */
    public function countEffectiveDays(DateRange $range): int
    {
        $count = 0;
        $cursor = $range->start->startOfDay();
        $limit = $range->end->startOfDay();

        while ($cursor->lte($limit)) {
            $isWorking = true;

            // Check working hours
            if ($this->workingHours && !$this->workingHours->isWorkingDay($cursor->dayOfWeekIso)) {
                $isWorking = false;
            }

            // Check holidays
            if ($isWorking && $this->holidayCalendar && $this->holidayCalendar->isHoliday($cursor)) {
                $isWorking = false;
            }

            if ($isWorking) {
                $count++;
            }

            $cursor = $cursor->addDay();
        }

        return $count;
    }

    /**
     * Generate a calendar grid for a given month containing occupancy data.
     *
     * @param int $year
     * @param int $month
     * @param array<int, DateRange> $occupied
     * @return array<int, array{date: string, day_of_week: int, is_occupied: bool, is_holiday: bool, is_working_day: bool}>
     */
    public function generateMonthGrid(int $year, int $month, array $occupied = []): array
    {
        $start = CarbonImmutable::create($year, $month, 1)->startOfDay();
        $end = $start->endOfMonth();
        $grid = [];

        $cursor = $start;
        while ($cursor->lte($end)) {
            $isOccupied = false;
            foreach ($occupied as $range) {
                if ($range->contains($cursor)) {
                    $isOccupied = true;
                    break;
                }
            }

            $grid[] = [
                'date' => $cursor->toDateString(),
                'day_of_week' => $cursor->dayOfWeekIso,
                'is_occupied' => $isOccupied,
                'is_holiday' => $this->holidayCalendar?->isHoliday($cursor) ?? false,
                'is_working_day' => $this->workingHours?->isWorkingDay($cursor->dayOfWeekIso) ?? true,
            ];

            $cursor = $cursor->addDay();
        }

        return $grid;
    }
}
