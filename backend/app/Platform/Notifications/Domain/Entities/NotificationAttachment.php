<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationAttachment extends Model
{
    protected $table = 'notification_attachments';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'dispatch_id',
        'asset_id',
    ];

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(NotificationDispatch::class, 'dispatch_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(\App\Platform\DAM\Domain\Entities\Asset::class, 'asset_id');
    }
}
