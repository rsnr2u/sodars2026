<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Enums\CreativeStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * Checks if a campaign has at least one approved creative and no pending or rejected current creatives.
 */
class CampaignCreativesApprovedSpecification implements SpecificationInterface
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof Campaign) {
            return false;
        }

        $creatives = $candidate->creatives;

        if ($creatives->isEmpty()) {
            return false;
        }

        // Must have at least one approved, and no rejected or pending creatives
        $hasApproved = false;
        foreach ($creatives as $creative) {
            if ($creative->status === CreativeStatus::Rejected) {
                return false;
            }
            if ($creative->status === CreativeStatus::Pending) {
                return false;
            }
            if ($creative->status === CreativeStatus::Approved) {
                $hasApproved = true;
            }
        }

        return $hasApproved;
    }

    public function toQuery(Builder $builder): Builder
    {
        return $builder->whereHas('creatives', function ($query) {
            $query->where('status', CreativeStatus::Approved->value);
        })->whereDoesntHave('creatives', function ($query) {
            $query->whereIn('status', [CreativeStatus::Pending->value, CreativeStatus::Rejected->value]);
        });
    }
}
