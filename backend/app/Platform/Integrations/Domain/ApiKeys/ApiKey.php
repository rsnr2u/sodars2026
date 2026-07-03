<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Domain\ApiKeys;

use App\Core\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    use HasUuid;

    protected $table = 'developer_api_keys';

    protected $fillable = [
        'user_id',
        'name',
        'key_prefix',
        'secret_hash',
        'scopes',
        'last_used_at',
        'last_ip',
        'last_user_agent',
        'expires_at',
        'revoked_at',
        'is_active',
    ];

    protected $casts = [
        'scopes' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes ?? [], true);
    }
}
