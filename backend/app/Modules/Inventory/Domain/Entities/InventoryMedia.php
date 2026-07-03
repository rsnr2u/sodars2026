<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Platform\Shared\Domain\Entities\MediaLibrary;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMedia extends BaseModel
{
    protected $table = 'inventory_media';

    protected $fillable = [
        'inventory_id',
        'media_id',
        'media_type',
        'display_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'display_order' => 'integer',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function mediaLibrary(): BelongsTo
    {
        return $this->belongsTo(MediaLibrary::class, 'media_id');
    }
}
