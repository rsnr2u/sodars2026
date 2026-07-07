<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\ValueObjects;

class ETAEstimate
{
    public function __construct(
        public readonly string $estimatedArrivalTime,
        public readonly float $remainingDistanceMeters,
        public readonly int $remainingDurationSeconds
    ) {}

    public function toArray(): array
    {
        return [
            'estimated_arrival_time' => $this->estimatedArrivalTime,
            'remaining_distance_meters' => $this->remainingDistanceMeters,
            'remaining_duration_seconds' => $this->remainingDurationSeconds,
        ];
    }
}
