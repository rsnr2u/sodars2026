<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Pipelines\Stages;

use Closure;
use Illuminate\Validation\ValidationException;

class ValidateInput
{
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];

        if (empty($dto->items)) {
            throw ValidationException::withMessages([
                'items' => ['Booking items cannot be empty.'],
            ]);
        }

        return $next($passable);
    }
}
