<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderStaff extends BaseModel
{
    protected $table = 'provider_staff';

    protected $fillable = [
        'provider_id',
        'user_id',
        'is_primary',
        'is_active',
        'joined_at',
        'left_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
