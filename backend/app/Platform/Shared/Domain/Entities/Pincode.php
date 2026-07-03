<?php

declare(strict_types=1);

namespace App\Platform\Shared\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pincode extends Model
{
    use HasUuid;

    protected $table = 'pincodes';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'city_id',
        'code',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
