<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Platform\Scheduling\DateRange;
use Illuminate\Database\Eloquent\Builder;

/**
 * Validates that a campaign's flight dates comply with constraints.
 */
class CampaignDateRangeValidationSpecification implements SpecificationInterface
{
    public function __construct(
        protected DateRange $validRange
    ) {}

    /**
     * Check if the candidate Campaign is fully enclosed within the valid flight range.
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof Campaign) {
            return false;
        }

        $campaignRange = new DateRange($candidate->start_date, $candidate->end_date);
        return $this->validRange->encloses($campaignRange);
    }

    public function toQuery(Builder $builder): Builder
    {
        return $builder->where('start_date', '>=', $this->validRange->start->toDateString())
            ->where('end_date', '<=', $this->validRange->end->toDateString());
    }
}
