<?php

declare(strict_types=1);

namespace App\Platform\Audit\Domain\Entities;

use App\Core\Traits\HasUuid;
use App\Models\User;
use App\Platform\Identity\Domain\Entities\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEvent extends Model
{
    use HasUuid;

    protected $table = 'audit_events';

    public $timestamps = false; // We use occurred_at and created_at manually

    protected $fillable = [
        'organization_id',
        'user_id',
        'actor_name',
        'category',
        'event_type',
        'event_version',
        'occurred_at',
        'auditable_type',
        'auditable_id',
        'before_snapshot',
        'after_snapshot',
        'description',
        'risk_level',
        'ip_address',
        'user_agent',
        'device_type',
        'trace_id',
        'correlation_id',
        'request_id',
        'session_id',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'before_snapshot' => 'array',
        'after_snapshot' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Prevent updates to keep audit events immutable.
     */
    public static function boot(): void
    {
        parent::boot();

        static::updating(function ($model) {
            throw new \RuntimeException('Audit events are immutable and cannot be updated.');
        });

        static::deleting(function ($model) {
            throw new \RuntimeException('Audit events are immutable and cannot be deleted.');
        });
    }
}
