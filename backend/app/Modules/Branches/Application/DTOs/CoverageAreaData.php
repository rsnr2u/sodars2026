<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\DTOs;

use Illuminate\Http\Request;

class CoverageAreaData
{
    public function __construct(
        public readonly string $countryId,
        public readonly string $stateId,
        public readonly ?string $districtId,
        public readonly string $cityId
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            countryId: $request->input('country_id'),
            stateId: $request->input('state_id'),
            districtId: $request->input('district_id'),
            cityId: $request->input('city_id')
        );
    }
}
