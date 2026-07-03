<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use Illuminate\Database\Eloquent\Builder;

class PendingVerificationSpecification implements SpecificationInterface
{
    /**
     * Check if provider is pending.
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $candidate instanceof Provider && $candidate->status === ProviderStatus::Pending;
    }

    /**
     * Apply status condition to query.
     */
    public function toQuery(Builder $builder): Builder
    {
        return $builder->where('status', ProviderStatus::Pending->value);
    }
}
