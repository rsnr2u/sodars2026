<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\ValueObjects;

class AssignmentCandidate
{
    public function __construct(
        public readonly string $resourceId,
        public readonly float $score,
        public readonly bool $skillsMatch,
        public readonly ?float $distanceMeters = null
    ) {}

    public function toArray(): array
    {
        return [
            'resource_id' => $this->resourceId,
            'score' => $this->score,
            'skills_match' => $this->skillsMatch,
            'distance_meters' => $this->distanceMeters,
        ];
    }
}
