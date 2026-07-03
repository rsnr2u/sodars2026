<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Pipelines\Stages;

use Closure;
use Illuminate\Validation\ValidationException;

class ValidateInputStage
{
    /**
     * Validate input data constraints.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];
        $errors = [];

        if (empty(trim($dto->displayName))) {
            $errors['display_name'] = ['Display name is required.'];
        }

        if (empty(trim($dto->providerId))) {
            $errors['provider_id'] = ['Provider ID is required.'];
        }

        if ($dto->latitude === 0.0) {
            $errors['latitude'] = ['GPS Latitude cannot be zero.'];
        }

        if ($dto->longitude === 0.0) {
            $errors['longitude'] = ['GPS Longitude cannot be zero.'];
        }

        if (empty(trim($dto->normalizedAddress))) {
            $errors['normalized_address'] = ['Normalized address is required.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $next($passable);
    }
}
