<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Models\User;
use App\Platform\Notifications\Domain\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationDispatch extends BaseModel
{
    protected $table = 'notification_dispatches';

    protected $fillable = [
        'template_id',
        'template_version_id',
        'recipient_id',
        'recipient_contact',
        'channel',
        'status',
        'context_snapshot',
        'send_at',
        'expires_at',
        'read_at',
    ];

    protected $casts = [
        'status' => NotificationStatus::class,
        'context_snapshot' => 'array',
        'send_at' => 'datetime',
        'expires_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function templateVersion(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplateVersion::class, 'template_version_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(NotificationAttempt::class, 'dispatch_id')->orderBy('attempt_number', 'asc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(NotificationAttachment::class, 'dispatch_id');
    }
}
