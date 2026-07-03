<?php

declare(strict_types=1);

namespace App\Modules\Branches\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Platform\Shared\Domain\Entities\City;
use App\Platform\Shared\Domain\Entities\Country;
use App\Platform\Shared\Domain\Entities\District;
use App\Platform\Shared\Domain\Entities\State;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchCoverageArea extends BaseModel
{
    protected $table = 'branch_coverage_areas';

    protected $fillable = [
        'branch_id',
        'country_id',
        'state_id',
        'district_id',
        'city_id',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
