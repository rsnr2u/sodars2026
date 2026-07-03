<?php

declare(strict_types=1);

namespace App\Platform\Scheduling;

use Carbon\CarbonImmutable;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Represents a daily operational time window (e.g. screen active 06:00–22:00).
 */
final class TimeSlot implements JsonSerializable
{
    public readonly string $startTime; // HH:MM format
    public readonly string $endTime;   // HH:MM format
    public readonly ?int $slotIndex;

    public function __construct(string $startTime, string $endTime, ?int $slotIndex = null)
    {
        if (!preg_match('/^\d{2}:\d{2}$/', $startTime) || !preg_match('/^\d{2}:\d{2}$/', $endTime)) {
            throw new InvalidArgumentException('TimeSlot times must be in HH:MM format.');
        }

        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->slotIndex = $slotIndex;
    }

    /**
     * Duration in minutes.
     */
    public function durationMinutes(): int
    {
        $start = CarbonImmutable::createFromFormat('H:i', $this->startTime);
        $end = CarbonImmutable::createFromFormat('H:i', $this->endTime);

        if (!$start || !$end) {
            return 0;
        }

        return (int) $start->diffInMinutes($end);
    }

    /**
     * Check if a given time falls within this slot.
     */
    public function containsTime(string $time): bool
    {
        return $time >= $this->startTime && $time <= $this->endTime;
    }

    /**
     * @return array{start_time: string, end_time: string, slot_index: int|null, duration_minutes: int}
     */
    public function toArray(): array
    {
        return [
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'slot_index' => $this->slotIndex,
            'duration_minutes' => $this->durationMinutes(),
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
