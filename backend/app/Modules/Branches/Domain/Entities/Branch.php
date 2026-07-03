<?php

declare(strict_types=1);

namespace App\Modules\Branches\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Branches\Domain\Enums\BranchStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends BaseModel
{
    protected $table = 'branches';

    protected $fillable = [
        'name',
        'code',
        'timezone',
        'currency_code',
        'markup_percentage',
        'support_email',
        'support_phone',
        'status',
    ];

    protected $casts = [
        'markup_percentage' => 'integer',
        'status' => BranchStatus::class,
    ];

    public function members(): HasMany
    {
        return $this->hasMany(BranchUser::class, 'branch_id');
    }

    public function coverageAreas(): HasMany
    {
        return $this->hasMany(BranchCoverageArea::class, 'branch_id');
    }
}
