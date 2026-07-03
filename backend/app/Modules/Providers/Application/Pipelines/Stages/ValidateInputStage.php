<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Pipelines\Stages;

use Closure;
use Illuminate\Validation\ValidationException;

class ValidateInputStage
{
    /**
     * Validate raw fields are provided.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];
        $errors = [];

        if (empty(trim($dto->companyName))) {
            $errors['company_name'] = ['Company name is required.'];
        }

        if (empty(trim($dto->registrationNumber))) {
            $errors['registration_number'] = ['Registration number is required.'];
        }

        if (empty(trim($dto->email))) {
            $errors['email'] = ['Email address is required.'];
        }

        if (empty(trim($dto->city))) {
            $errors['city'] = ['City is required.'];
        }

        if (empty(trim($dto->state))) {
            $errors['state'] = ['State is required.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $next($passable);
    }
}
