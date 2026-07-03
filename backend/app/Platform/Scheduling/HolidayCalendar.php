<?php

declare(strict_types=1);

namespace App\Platform\Scheduling;

use Carbon\Carbon;
use Carbon\CarbonImmutable;

/**
 * Manages national/regional holiday lookups.
 *
 * Holidays can be loaded from configuration or database.
 */
final class HolidayCalendar
{
    /** @var array<string, string> Date (Y-m-d) => Holiday name */
    private array $holidays = [];

    /**
     * Register a holiday.
     */
    public function addHoliday(string $date, string $name): void
    {
        $this->holidays[CarbonImmutable::parse($date)->toDateString()] = $name;
    }

    /**
     * Register multiple holidays.
     *
     * @param array<string, string> $holidays Date => Name
     */
    public function addHolidays(array $holidays): void
    {
        foreach ($holidays as $date => $name) {
            $this->addHoliday($date, $name);
        }
    }

    /**
     * Check if a date is a holiday.
     */
    public function isHoliday(Carbon|CarbonImmutable|string $date): bool
    {
        $key = $date instanceof (Carbon::class) || $date instanceof CarbonImmutable
            ? $date->toDateString()
            : CarbonImmutable::parse($date)->toDateString();

        return isset($this->holidays[$key]);
    }

    /**
     * Get the holiday name for a specific date.
     */
    public function getHolidayName(Carbon|CarbonImmutable|string $date): ?string
    {
        $key = $date instanceof (Carbon::class) || $date instanceof CarbonImmutable
            ? $date->toDateString()
            : CarbonImmutable::parse($date)->toDateString();

        return $this->holidays[$key] ?? null;
    }

    /**
     * Count holidays within a date range.
     */
    public function countHolidaysInRange(DateRange $range): int
    {
        $count = 0;
        foreach ($this->holidays as $dateStr => $_name) {
            $date = CarbonImmutable::parse($dateStr);
            if ($range->contains($date)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Get all holidays within a date range.
     *
     * @return array<string, string>
     */
    public function getHolidaysInRange(DateRange $range): array
    {
        $result = [];
        foreach ($this->holidays as $dateStr => $name) {
            $date = CarbonImmutable::parse($dateStr);
            if ($range->contains($date)) {
                $result[$dateStr] = $name;
            }
        }
        return $result;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->holidays;
    }
}
