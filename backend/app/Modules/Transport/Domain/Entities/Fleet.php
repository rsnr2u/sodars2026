<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fleet extends BaseBusinessModel
{
    protected $table = 'fleets';

    protected $fillable = [
        'organization_id',
        'name',
        'code',
    ];

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'fleet_id');
    }
}
