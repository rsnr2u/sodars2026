<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Pipelines\Stages;

use App\Modules\Providers\Domain\Entities\ProviderAddress;
use Closure;

class CreateAddressStage
{
    /**
     * Persist address details.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];
        $provider = $passable['provider'];

        ProviderAddress::create([
            'provider_id' => $provider->id,
            'country_id' => $passable['country_id'],
            'state_id' => $passable['state_id'],
            'district_id' => $passable['district_id'],
            'city_id' => $passable['city_id'],
            'pincode_id' => $passable['pincode_id'],
            'address_line1' => $dto->city . ', ' . $dto->state,
            'is_primary' => true,
        ]);

        return $next($passable);
    }
}
