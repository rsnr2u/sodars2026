<?php

declare(strict_types=1);

namespace App\Platform\DAM\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Platform\DAM\Domain\Enums\AttachmentRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends BaseModel
{
    protected $table = 'dam_attachments';

    protected $fillable = [
        'asset_id',
        'attachable_type',
        'attachable_id',
        'attachment_role',
    ];

    protected $casts = [
        'attachment_role' => AttachmentRole::class,
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
