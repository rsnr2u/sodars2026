<?php

declare(strict_types=1);

namespace App\Platform\Scheduling;

/**
 * Validates date range conflicts against existing timelines.
 *
 * Reusable across Campaigns, Bookings, Inventory Availability,
 * and Maintenance Windows.
 */
final class ConflictDetector
{
    /**
     * Detect all conflicts between a candidate range and a set of existing ranges.
     *
     * @param DateRange $candidate The proposed new range.
     * @param array<int, DateRange> $existing The existing reserved ranges.
     * @return array<int, DateRange> Conflicting ranges.
     */
    public function detectConflicts(DateRange $candidate, array $existing): array
    {
        return array_values(array_filter($existing, function (DateRange $range) use ($candidate) {
            return $candidate->overlaps($range);
        }));
    }

    /**
     * Check if a candidate range has any conflicts.
     *
     * @param DateRange $candidate
     * @param array<int, DateRange> $existing
     */
    public function hasConflicts(DateRange $candidate, array $existing): bool
    {
        return count($this->detectConflicts($candidate, $existing)) > 0;
    }

    /**
     * Find the first available gap in a timeline for a given duration.
     *
     * @param DateRange $searchWindow The window to search within.
     * @param int $requiredDays The minimum number of contiguous days needed.
     * @param array<int, DateRange> $occupied Already occupied ranges.
     * @return DateRange|null The first available gap, or null if none found.
     */
    public function findFirstAvailableGap(DateRange $searchWindow, int $requiredDays, array $occupied): ?DateRange
    {
        // Sort occupied ranges by start date
        usort($occupied, function (DateRange $a, DateRange $b) {
            return $a->start->timestamp <=> $b->start->timestamp;
        });

        $cursor = $searchWindow->start;

        foreach ($occupied as $range) {
            if (!$searchWindow->overlaps($range)) {
                continue;
            }

            // Check gap before this occupied range
            if ($cursor->lt($range->start)) {
                $gapDays = (int) $cursor->diffInDays($range->start);
                if ($gapDays >= $requiredDays) {
                    return new DateRange($cursor, $cursor->addDays($requiredDays - 1));
                }
            }

            // Move cursor past the occupied range
            if ($range->end->gte($cursor)) {
                $cursor = $range->end->addDay()->startOfDay();
            }
        }

        // Check remaining gap after last occupied range
        if ($cursor->lte($searchWindow->end)) {
            $remainingDays = (int) $cursor->diffInDays($searchWindow->end) + 1;
            if ($remainingDays >= $requiredDays) {
                return new DateRange($cursor, $cursor->addDays($requiredDays - 1));
            }
        }

        return null;
    }
}
