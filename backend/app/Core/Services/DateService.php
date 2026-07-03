<?php

declare(strict_types=1);

namespace App\Core\Services;

use Carbon\Carbon;
use DateTimeImmutable;

class DateService
{
    protected string $timezone;

    public function __construct()
    {
        $this->timezone = config('app.timezone', 'UTC');
    }

    /**
     * Parse date string into UTC DateTimeImmutable object.
     */
    public function parse(string $dateString): DateTimeImmutable
    {
        $carbon = Carbon::parse($dateString, $this->timezone)->setTimezone('UTC');

        return DateTimeImmutable::createFromMutable($carbon->toDateTime());
    }

    /**
     * Get the current date and time.
     */
    public function now(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromMutable(Carbon::now('UTC')->toDateTime());
    }

    /**
     * Calculate campaign duration in days.
     */
    public function getDurationInDays(string $startDate, string $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        return (int) $start->diffInDays($end) + 1;
    }
}
