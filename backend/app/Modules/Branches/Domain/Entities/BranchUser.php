<?php

declare(strict_types=1);

namespace App\Modules\Branches\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchUser extends BaseModel
{
    protected $table = 'branch_users';

    protected $fillable = [
        'branch_id',
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
