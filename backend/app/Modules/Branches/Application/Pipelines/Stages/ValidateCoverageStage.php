<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Pipelines\Stages;

use Closure;

class ValidateCoverageStage
{
    /**
     * Pass-through validation stage for coverage boundaries.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        // Coverage boundaries are verified during specific add/remove coverage actions.
        // This stage acts as a placeholder for any pre-creation coverage rules if added in the future.
        return $next($passable);
    }
}
