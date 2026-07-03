<?php

declare(strict_types=1);

namespace App\Platform\Scheduling;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Immutable value object representing a contiguous date range.
 *
 * Used across Campaigns, Bookings, Inventory Availability,
 * and Maintenance Windows for overlap detection and calendar math.
 */
final class DateRange implements JsonSerializable
{
    public readonly CarbonImmutable $start;
    public readonly CarbonImmutable $end;

    public function __construct(Carbon|CarbonImmutable|string $start, Carbon|CarbonImmutable|string $end)
    {
        $this->start = $start instanceof CarbonImmutable
            ? $start
            : CarbonImmutable::parse($start)->startOfDay();

        $this->end = $end instanceof CarbonImmutable
            ? $end
            : CarbonImmutable::parse($end)->endOfDay();

        if ($this->start->greaterThan($this->end)) {
            throw new InvalidArgumentException('DateRange start must be before or equal to end.');
        }
    }

    /**
     * Check if this range overlaps with another.
     */
    public function overlaps(self $other): bool
    {
        return $this->start->lte($other->end) && $this->end->gte($other->start);
    }

    /**
     * Check if a specific date falls within this range.
     */
    public function contains(Carbon|CarbonImmutable $date): bool
    {
        $check = $date instanceof CarbonImmutable ? $date : CarbonImmutable::parse($date);

        return $check->gte($this->start) && $check->lte($this->end);
    }

    /**
     * Duration in whole calendar days (inclusive).
     */
    public function durationInDays(): int
    {
        return (int) $this->start->startOfDay()->diffInDays($this->end->startOfDay()) + 1;
    }

    /**
     * Return the intersection of two ranges, or null if no overlap.
     */
    public function intersect(self $other): ?self
    {
        if (!$this->overlaps($other)) {
            return null;
        }

        return new self(
            $this->start->max($other->start),
            $this->end->min($other->end)
        );
    }

    /**
     * Check if this range fully contains another range.
     */
    public function encloses(self $other): bool
    {
        return $this->start->lte($other->start) && $this->end->gte($other->end);
    }

    /**
     * Split the range into daily segments.
     *
     * @return array<int, self>
     */
    public function toDailySegments(): array
    {
        $segments = [];
        $cursor = $this->start->startOfDay();
        $limit = $this->end->startOfDay();

        while ($cursor->lte($limit)) {
            $segments[] = new self($cursor, $cursor->endOfDay());
            $cursor = $cursor->addDay();
        }

        return $segments;
    }

    /**
     * @return array{start: string, end: string, duration_days: int}
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start->toDateString(),
            'end' => $this->end->toDateString(),
            'duration_days' => $this->durationInDays(),
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
