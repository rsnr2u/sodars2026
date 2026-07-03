<?php

declare(strict_types=1);

namespace App\Platform\Scheduling;

use JsonSerializable;

/**
 * Maintains an ordered list of DateRange segments with occupancy tracking.
 *
 * Used to visualize calendar grids showing free/occupied intervals.
 */
final class Timeline implements JsonSerializable
{
    /** @var array<int, array{range: DateRange, label: string, metadata: array<string, mixed>}> */
    private array $entries = [];

    /**
     * Add an occupied segment to the timeline.
     *
     * @param array<string, mixed> $metadata
     */
    public function addEntry(DateRange $range, string $label, array $metadata = []): void
    {
        $this->entries[] = [
            'range' => $range,
            'label' => $label,
            'metadata' => $metadata,
        ];
    }

    /**
     * Find all entries that overlap with the given range.
     *
     * @return array<int, array{range: DateRange, label: string, metadata: array<string, mixed>}>
     */
    public function getConflicts(DateRange $candidate): array
    {
        return array_values(array_filter($this->entries, function (array $entry) use ($candidate) {
            return $entry['range']->overlaps($candidate);
        }));
    }

    /**
     * Check if the timeline has any conflicts with the given range.
     */
    public function hasConflicts(DateRange $candidate): bool
    {
        return count($this->getConflicts($candidate)) > 0;
    }

    /**
     * Get the total number of occupied days across all entries.
     */
    public function totalOccupiedDays(): int
    {
        return array_reduce($this->entries, function (int $carry, array $entry) {
            return $carry + $entry['range']->durationInDays();
        }, 0);
    }

    /**
     * @return array<int, array{range: array, label: string, metadata: array<string, mixed>}>
     */
    public function toArray(): array
    {
        return array_map(function (array $entry) {
            return [
                'range' => $entry['range']->toArray(),
                'label' => $entry['label'],
                'metadata' => $entry['metadata'],
            ];
        }, $this->entries);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
