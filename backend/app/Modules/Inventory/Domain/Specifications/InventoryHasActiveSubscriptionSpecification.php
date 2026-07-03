<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use Illuminate\Database\Eloquent\Builder;

class InventoryHasActiveSubscriptionSpecification implements SpecificationInterface
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof Inventory) {
            return false;
        }

        $provider = $candidate->provider;
        if (!$provider) {
            return false;
        }

        return $provider->status === ProviderStatus::Verified;
    }

    public function toQuery(Builder $builder): Builder
    {
        return $builder->whereHas('provider', function ($query) {
            $query->where('status', ProviderStatus::Verified->value);
        });
    }
}
