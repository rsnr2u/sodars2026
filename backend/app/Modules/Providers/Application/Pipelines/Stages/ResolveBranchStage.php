<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Pipelines\Stages;

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
     * Resolve default managing branch by checking city coverage lists.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];

        // 1. Resolve geography entities
        $state = State::where('name', 'LIKE', $dto->state)->first() ?? State::first();
        $city = City::where('name', 'LIKE', $dto->city)->first() ?? City::first();
        
        $country = $state?->country ?? Country::first();
        $district = $city?->district ?? District::first();

        $pincode = null;
        if ($dto->pincode) {
            $pincode = Pincode::where('code', $dto->pincode)->first();
        }

        // 2. Resolve branch routing
        $branchId = null;
        if ($city) {
            $coverage = DB::table('branch_coverage_areas')->where('city_id', $city->id)->first();
            $branchId = $coverage?->branch_id;
        }

        if (!$branchId) {
            $fallbackBranch = Branch::where('status', 'active')->first() ?? Branch::first();
            $branchId = $fallbackBranch?->id;
        }

        $passable['default_branch_id'] = $branchId;
        $passable['city_id'] = $city?->id;
        $passable['state_id'] = $state?->id;
        $passable['district_id'] = $district?->id;
        $passable['country_id'] = $country?->id;
        $passable['pincode_id'] = $pincode?->id;

        return $next($passable);
    }
}
