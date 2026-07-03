<?php

declare(strict_types=1);

namespace App\Platform\Shared\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasUuid;

    protected $table = 'audit_logs';

    public $incrementing = false;

    protected $keyType = 'string';

    // Disable standard updated_at since logs are insert-only
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
