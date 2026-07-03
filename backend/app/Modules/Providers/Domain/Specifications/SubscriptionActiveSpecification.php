<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Providers\Domain\Entities\Provider;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionActiveSpecification implements SpecificationInterface
{
    /**
     * Check if candidate has active subscription.
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof Provider) {
            return false;
        }

        $activeSub = $candidate->activeSubscription;
        return $activeSub !== null && $activeSub->is_active === true && $activeSub->ends_at?->isFuture() !== false;
    }

    /**
     * Filter query to active subscriptions.
     */
    public function toQuery(Builder $builder): Builder
    {
        return $builder->whereHas('subscriptions', function ($query) {
            $query->where('is_active', true)
                ->where('ends_at', '>', now());
        });
    }
}
