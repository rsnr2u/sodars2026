<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Providers\Domain\Entities\Provider;
use Illuminate\Database\Eloquent\Builder;

class ProviderWithExpiredDocumentsSpecification implements SpecificationInterface
{
    /**
     * Check if candidate has expired documents.
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof Provider) {
            return false;
        }

        return $candidate->documents()->where('is_current', true)->where('expires_at', '<', now())->exists();
    }

    /**
     * Filter query to expired documents.
     */
    public function toQuery(Builder $builder): Builder
    {
        return $builder->whereHas('documents', function ($query) {
            $query->where('is_current', true)->where('expires_at', '<', now());
        });
    }
}
