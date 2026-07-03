<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Domain\Entities;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationTemplateVersion extends BaseModel
{
    protected $table = 'notification_template_versions';

    protected $fillable = [
        'template_id',
        'version_number',
        'subject',
        'content',
        'is_active',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'content' => 'array',
        'is_active' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }
}
