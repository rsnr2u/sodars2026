<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Providers\Domain\Entities\Provider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProviderWithinCoverageSpecification implements SpecificationInterface
{
    public function __construct(
        protected string $branchId
    ) {}

    /**
     * Check if candidate is covered by branch.
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof Provider) {
            return false;
        }

        $address = $candidate->primaryAddress;
        if (!$address) {
            return false;
        }

        return DB::table('branch_coverage_areas')
            ->where('branch_id', $this->branchId)
            ->where('city_id', $address->city_id)
            ->exists();
    }

    /**
     * Apply coverage filtering constraint to query.
     */
    public function toQuery(Builder $builder): Builder
    {
        return $builder->whereHas('addresses', function ($query) {
            $query->where('is_primary', true)
                ->whereIn('city_id', function ($sub) {
                    $sub->select('city_id')
                        ->from('branch_coverage_areas')
                        ->where('branch_id', $this->branchId);
                });
        });
    }
}
