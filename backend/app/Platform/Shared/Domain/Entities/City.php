<?php

declare(strict_types=1);

namespace App\Platform\Shared\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasUuid;

    protected $table = 'cities';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'district_id',
        'name',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function pincodes(): HasMany
    {
        return $this->hasMany(Pincode::class);
    }
}
