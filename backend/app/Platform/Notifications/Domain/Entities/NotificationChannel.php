<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

class NotificationChannel extends Model
{
    protected $table = 'notification_channels';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'key',
        'driver',
        'is_enabled',
        'priority',
        'retry_attempts',
        'timeout_seconds',
        'configuration',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'priority' => 'integer',
        'retry_attempts' => 'integer',
        'timeout_seconds' => 'integer',
        'configuration' => 'array',
    ];
}
