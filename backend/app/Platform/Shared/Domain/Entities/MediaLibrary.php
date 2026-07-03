<?php

declare(strict_types=1);

namespace App\Platform\Shared\Domain\Entities;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaLibrary extends BaseModel
{
    protected $table = 'media_library';

    protected $fillable = [
        'file_name',
        'file_path',
        'mime_type',
        'file_size_bytes',
        'mediable_type',
        'mediable_id',
    ];

    /**
     * Get the owning mediable model polymorphic relationship.
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
}
