<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Pipelines\Stages;

use App\Models\User;
use Closure;
use Illuminate\Validation\ValidationException;

class ResolveCustomerStage
{
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];

        $customer = User::find($dto->customerId);
        if (!$customer) {
            throw ValidationException::withMessages([
                'customer_id' => ['Customer user account not found.'],
            ]);
        }

        $passable['customer'] = $customer;

        return $next($passable);
    }
}
