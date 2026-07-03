<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use Illuminate\Database\Eloquent\Builder;

class ProviderEligibleForMarketplaceSpecification implements SpecificationInterface
{
    /**
     * Check if candidate matches all eligibility constraints for marketplace listing.
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return (new VerifiedProviderSpecification())->isSatisfiedBy($candidate) &&
            (new MarketplaceEnabledSpecification())->isSatisfiedBy($candidate) &&
            (new SubscriptionActiveSpecification())->isSatisfiedBy($candidate) &&
            !(new ProviderWithExpiredDocumentsSpecification())->isSatisfiedBy($candidate);
    }

    /**
     * Apply composite query rules.
     */
    public function toQuery(Builder $builder): Builder
    {
        $builder = (new VerifiedProviderSpecification())->toQuery($builder);
        $builder = (new MarketplaceEnabledSpecification())->toQuery($builder);
        $builder = (new SubscriptionActiveSpecification())->toQuery($builder);

        return $builder->whereDoesntHave('documents', function ($query) {
            $query->where('is_current', true)->where('expires_at', '<', now());
        });
    }
}
