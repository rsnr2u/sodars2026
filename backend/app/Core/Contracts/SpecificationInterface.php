<?php

declare(strict_types=1);

namespace App\Core\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface SpecificationInterface
{
    /**
     * Check if a candidate object satisfies the specification.
     */
    public function isSatisfiedBy(mixed $candidate): bool;

    /**
     * Compile or apply the specification criteria directly onto an Eloquent Query Builder.
     */
    public function toQuery(Builder $builder): Builder;
}
