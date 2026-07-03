<?php

declare(strict_types=1);

namespace App\Platform\Scheduling;

use JsonSerializable;

/**
 * Tracks active operational time limits for screens or assets.
 *
 * E.g. a screen runs 06:00–22:00, a provider office is open 09:00–18:00.
 */
final class WorkingHours implements JsonSerializable
{
    /** @var array<int, TimeSlot> Indexed by day-of-week (1=Mon, 7=Sun) */
    private array $schedule = [];

    /**
     * Set working hours for a specific day of the week.
     *
     * @param int $dayOfWeek ISO day of week (1=Monday, 7=Sunday)
     */
    public function setDay(int $dayOfWeek, string $startTime, string $endTime): void
    {
        $this->schedule[$dayOfWeek] = new TimeSlot($startTime, $endTime);
    }

    /**
     * Set uniform working hours for all weekdays (Mon-Fri).
     */
    public function setWeekdays(string $startTime, string $endTime): void
    {
        for ($day = 1; $day <= 5; $day++) {
            $this->setDay($day, $startTime, $endTime);
        }
    }

    /**
     * Set uniform working hours for all days (Mon-Sun).
     */
    public function setAllDays(string $startTime, string $endTime): void
    {
        for ($day = 1; $day <= 7; $day++) {
            $this->setDay($day, $startTime, $endTime);
        }
    }

    /**
     * Check if a day has defined working hours.
     */
    public function isWorkingDay(int $dayOfWeek): bool
    {
        return isset($this->schedule[$dayOfWeek]);
    }

    /**
     * Get the TimeSlot for a specific day.
     */
    public function getDay(int $dayOfWeek): ?TimeSlot
    {
        return $this->schedule[$dayOfWeek] ?? null;
    }

    /**
     * Count total working days in a DateRange.
     */
    public function countWorkingDaysInRange(DateRange $range): int
    {
        $count = 0;
        $cursor = $range->start->startOfDay();
        $limit = $range->end->startOfDay();

        while ($cursor->lte($limit)) {
            if ($this->isWorkingDay($cursor->dayOfWeekIso)) {
                $count++;
            }
            $cursor = $cursor->addDay();
        }

        return $count;
    }

    /**
     * @return array<int, array{start_time: string, end_time: string}>
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->schedule as $day => $slot) {
            $result[$day] = $slot->toArray();
        }
        return $result;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
