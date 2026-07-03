<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * Validates if a campaign is eligible for scheduling/running:
 * - Status is scheduled or running
 * - At least one target face assigned
 * - Creatives approved specification satisfies the campaign
 */
class CampaignEligibleForExecutionSpecification implements SpecificationInterface
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof Campaign) {
            return false;
        }

        // Must have inventory faces assigned
        if ($candidate->inventoryFaces()->count() === 0) {
            return false;
        }

        // Must have approved creatives
        $creativeSpec = new CampaignCreativesApprovedSpecification();
        if (!$creativeSpec->isSatisfiedBy($candidate)) {
            return false;
        }

        return in_array($candidate->status, [CampaignStatus::Scheduled, CampaignStatus::Running], true);
    }

    public function toQuery(Builder $builder): Builder
    {
        $creativeSpec = new CampaignCreativesApprovedSpecification();
        $builder = $creativeSpec->toQuery($builder);

        return $builder->whereHas('inventoryFaces')
            ->whereIn('status', [CampaignStatus::Scheduled->value, CampaignStatus::Running->value]);
    }
}
