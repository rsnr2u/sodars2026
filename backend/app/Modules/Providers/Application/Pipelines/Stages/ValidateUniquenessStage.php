<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Pipelines\Stages;

use App\Models\User;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;
use Closure;
use Illuminate\Validation\ValidationException;

class ValidateUniquenessStage
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerRepo
    ) {}

    /**
     * Check database records for registration number and email uniqueness.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];
        $errors = [];

        if ($this->providerRepo->findByRegNumber($dto->registrationNumber)) {
            $errors['registration_number'] = ['This registration number is already registered.'];
        }

        if (User::where('email', $dto->email)->exists()) {
            $errors['email'] = ['This email address is already in use.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $next($passable);
    }
}
