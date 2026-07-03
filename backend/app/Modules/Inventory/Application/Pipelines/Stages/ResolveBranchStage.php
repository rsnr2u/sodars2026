<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Pipelines\Stages;

use App\Modules\Branches\Domain\Entities\Branch;
use App\Platform\Shared\Domain\Entities\City;
use App\Platform\Shared\Domain\Entities\Country;
use App\Platform\Shared\Domain\Entities\District;
use App\Platform\Shared\Domain\Entities\Pincode;
use App\Platform\Shared\Domain\Entities\State;
use Closure;
use Illuminate\Support\Facades\DB;

class ResolveBranchStage
{
    /**
     * Resolve geography and managing branch coverage mapping.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $provider = $passable['provider'];

        // 1. Resolve geography entities
        $city = City::first();
        $district = $city?->district ?? District::first();
        $state = $city?->state ?? State::first();
        $country = $state?->country ?? Country::first();
        $pincode = Pincode::first();

        // 2. Resolve branch routing matching the city coverage
        $branchId = null;
        if ($city) {
            $coverage = DB::table('branch_coverage_areas')->where('city_id', $city->id)->first();
            $branchId = $coverage?->branch_id;
        }

        if (!$branchId) {
            // Fallback to provider's default branch
            $branchId = $provider->default_branch_id;
        }

        if (!$branchId) {
            $fallbackBranch = Branch::where('status', 'active')->first() ?? Branch::first();
            $branchId = $fallbackBranch?->id;
        }

        $passable['branch_id'] = $branchId;
        $passable['city_id'] = $city?->id;
        $passable['state_id'] = $state?->id;
        $passable['district_id'] = $district?->id;
        $passable['country_id'] = $country?->id;
        $passable['pincode_id'] = $pincode?->id;

        return $next($passable);
    }
}
