<?php

declare(strict_types=1);

namespace App\Core\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

final class DateRange
{
    private DateTimeImmutable $startDate;

    private DateTimeImmutable $endDate;

    public function __construct(DateTimeImmutable $startDate, DateTimeImmutable $endDate)
    {
        if ($startDate > $endDate) {
            throw new InvalidArgumentException('Start date must be before or equal to end date.');
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getDurationInDays(): int
    {
        return (int) $this->startDate->diff($this->endDate)->days + 1;
    }
}
