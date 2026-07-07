<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Services;

use App\Modules\Operations\Domain\Entities\OperationalResource;
use App\Modules\Operations\Domain\ValueObjects\AssignmentCandidate;
use Carbon\Carbon;

class AssignmentEngine
{
    public function __construct(
        protected AvailabilityEngine $availabilityEngine,
        protected CapacityEngine $capacityEngine
    ) {}

    /**
     * Evaluate a list of resources to determine match scoring and select the best fit candidate.
     *
     * @return array<int, AssignmentCandidate>
     */
    public function scoreCandidates(
        array $resources,
        array $requiredSkills,
        Carbon $start,
        Carbon $end
    ): array {
        $candidates = [];

        foreach ($resources as $resource) {
            /** @var OperationalResource $resource */

            // 1. Verify availability
            $isAvailable = $this->availabilityEngine->checkAvailability($resource, $start, $end);
            if (!$isAvailable) {
                continue;
            }

            // 2. Verify capacity
            $hasCapacity = $this->capacityEngine->verifyCapacity($resource, $start, $end);
            if (!$hasCapacity) {
                continue;
            }

            // 3. Match skills
            $resourceSkills = $resource->skills ?? [];
            $matchedSkills = array_intersect($requiredSkills, $resourceSkills);
            $skillsMatch = count($matchedSkills) === count($requiredSkills);

            // Compute score (e.g. skills weight, current utilization)
            $skillsScore = $skillsMatch ? 50.0 : (count($matchedSkills) * 10.0);
            $workloadScore = 50.0; // placeholder, high is better (low utilization)

            $overallScore = $skillsScore + $workloadScore;

            $candidates[] = new AssignmentCandidate(
                $resource->id,
                $overallScore,
                $skillsMatch,
                null
            );
        }

        // Sort descending by score
        usort($candidates, fn($a, $b) => $b->score <=> $a->score);

        return $candidates;
    }
}
