<?php

declare(strict_types=1);

namespace App\Platform\Identity\Domain\Entities;

use App\Core\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginSession extends Model
{
    use HasUuid;

    protected $table = 'login_sessions';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'location',
        'logged_in_at',
        'last_active_at',
        'logged_out_at',
        'is_revoked',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'last_active_at' => 'datetime',
        'logged_out_at' => 'datetime',
        'is_revoked' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
