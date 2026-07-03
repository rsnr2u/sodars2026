<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Pipelines\Stages;

use App\Modules\Providers\Domain\Entities\Provider;
use Closure;
use Illuminate\Validation\ValidationException;

class ResolveProviderStage
{
    /**
     * Resolve the provider entity and check status.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];

        $provider = Provider::find($dto->providerId);
        if (!$provider) {
            throw ValidationException::withMessages([
                'provider_id' => ['The specified provider does not exist.'],
            ]);
        }

        $passable['provider'] = $provider;

        return $next($passable);
    }
}
