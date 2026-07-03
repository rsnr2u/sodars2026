<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Platform\Notifications\Domain\Enums\NotificationCategory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationTemplate extends BaseModel
{
    protected $table = 'notification_templates';

    protected $fillable = [
        'key',
        'name',
        'category',
        'active_version_number',
    ];

    protected $casts = [
        'category' => NotificationCategory::class,
        'active_version_number' => 'integer',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(NotificationTemplateVersion::class, 'template_id')->orderBy('version_number', 'asc');
    }
}
