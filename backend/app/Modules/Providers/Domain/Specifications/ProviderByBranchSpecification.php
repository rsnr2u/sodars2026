<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Providers\Domain\Entities\Provider;
use Illuminate\Database\Eloquent\Builder;

class ProviderByBranchSpecification implements SpecificationInterface
{
    public function __construct(
        protected string $branchId
    ) {}

    /**
     * Check if candidate belongs to the specified branch.
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $candidate instanceof Provider && $candidate->default_branch_id === $this->branchId;
    }

    /**
     * Apply default_branch_id condition to query.
     */
    public function toQuery(Builder $builder): Builder
    {
        return $builder->where('default_branch_id', $this->branchId);
    }
}
