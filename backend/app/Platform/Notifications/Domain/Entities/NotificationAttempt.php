<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationAttempt extends Model
{
    protected $table = 'notification_attempts';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false; // Manually uses created_at timestamp column

    protected $fillable = [
        'id',
        'dispatch_id',
        'attempt_number',
        'provider_name',
        'provider_reference',
        'provider_response',
        'provider_status_code',
        'status',
        'error_message',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'attempt_number' => 'integer',
        'provider_status_code' => 'integer',
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(NotificationDispatch::class, 'dispatch_id');
    }
}
