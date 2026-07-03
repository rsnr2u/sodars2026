<?php

declare(strict_types=1);

namespace App\Platform\Shared\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasUuid;

    protected $table = 'countries';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'iso_code',
    ];

    /**
     * Get the country's states.
     */
    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }
}
