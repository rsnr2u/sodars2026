<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Inventory\Domain\Enums\AvailabilityStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAvailability extends BaseModel
{
    protected $table = 'inventory_availability';

    protected $fillable = [
        'inventory_face_id',
        'start_at',
        'end_at',
        'availability_status',
        'reason',
        'source',
        'remarks',
    ];

    protected $casts = [
        'availability_status' => AvailabilityStatus::class,
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function face(): BelongsTo
    {
        return $this->belongsTo(InventoryFace::class, 'inventory_face_id');
    }
}
