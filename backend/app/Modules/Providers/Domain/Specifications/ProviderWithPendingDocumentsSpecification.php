<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Specifications;

use App\Core\Contracts\SpecificationInterface;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Builder;

class ProviderWithPendingDocumentsSpecification implements SpecificationInterface
{
    /**
     * Check if candidate has pending documents.
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof Provider) {
            return false;
        }

        return $candidate->documents()->where('status', DocumentStatus::Pending)->exists();
    }

    /**
     * Filter query to pending documents.
     */
    public function toQuery(Builder $builder): Builder
    {
        return $builder->whereHas('documents', function ($query) {
            $query->where('status', DocumentStatus::Pending->value);
        });
    }
}
