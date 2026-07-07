<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\ValueObjects;

class RecurrencePattern
{
    public function __construct(
        public readonly string $frequency,
        public readonly int $interval = 1,
        public readonly array $byDays = [],
        public readonly array $exceptionDates = [],
        public readonly ?string $endsAt = null
    ) {}

    public function toArray(): array
    {
        return [
            'frequency' => $this->frequency,
            'interval' => $this->interval,
            'by_days' => $this->byDays,
            'exception_dates' => $this->exceptionDates,
            'ends_at' => $this->endsAt,
        ];
    }
}
