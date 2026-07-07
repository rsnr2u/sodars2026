<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\ValueObjects;

class OptimizationResult
{
    public function __construct(
        public readonly float $score,
        public readonly float $distanceScore,
        public readonly float $workloadScore,
        public readonly float $availabilityScore,
        public readonly float $etaScore,
        public readonly string $explanation,
        public readonly array $rejectedCandidates = []
    ) {}

    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'distance_score' => $this->distanceScore,
            'workload_score' => $this->workloadScore,
            'availability_score' => $this->availabilityScore,
            'eta_score' => $this->etaScore,
            'explanation' => $this->explanation,
            'rejected_candidates' => $this->rejectedCandidates,
        ];
    }
}
