<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Services;

use App\Modules\Operations\Domain\Entities\OperationalResource;
use App\Modules\Operations\Domain\ValueObjects\OptimizationResult;
use Carbon\Carbon;

class OptimizationEngine
{
    public function __construct(protected AssignmentEngine $assignmentEngine) {}

    /**
     * Compute candidate scores based on workload balances and target timing slots.
     */
    public function optimize(
        array $resources,
        array $requiredSkills,
        Carbon $start,
        Carbon $end
    ): OptimizationResult {
        $candidates = $this->assignmentEngine->scoreCandidates($resources, $requiredSkills, $start, $end);

        if (empty($candidates)) {
            return new OptimizationResult(
                0.0,
                0.0,
                0.0,
                0.0,
                0.0,
                'No available candidates matching constraint limits.',
                []
            );
        }

        // Select best candidate details
        $best = $candidates[0];
        $rejected = array_slice($candidates, 1);

        $workloadScore = 50.0;
        $availabilityScore = 50.0;
        $etaScore = 0.0; // GPS eta parameters not verified yet

        return new OptimizationResult(
            $best->score,
            0.0,
            $workloadScore,
            $availabilityScore,
            $etaScore,
            "Optimized selection for resource [{$best->resourceId}]. Matching skill checklist.",
            array_map(fn($c) => $c->toArray(), $rejected)
        );
    }
}
