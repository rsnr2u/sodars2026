<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Providers\Domain\Entities\Provider;
use Illuminate\Database\Eloquent\Builder;

class MarketplaceEnabledSpecification implements SpecificationInterface
{
    /**
     * Check if candidate is marketplace-enabled.
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $candidate instanceof Provider &&
            $candidate->settings !== null &&
            $candidate->settings->settings->marketplaceEnabled === true;
    }

    /**
     * Filter query to settings where marketplace_enabled is true.
     */
    public function toQuery(Builder $builder): Builder
    {
        return $builder->whereHas('settings', function ($query) {
            $query->whereJsonContains('settings->marketplace_enabled', true);
        });
    }
}
