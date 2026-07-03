<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Inventory\Domain\Enums\FacingDirection;
use App\Modules\Inventory\Domain\ValueObjects\PhysicalSpecification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryFace extends BaseModel
{
    protected $table = 'inventory_faces';

    protected $fillable = [
        'inventory_id',
        'face_code',
        'display_name',
        'facing_direction',
        'display_order',
        'physical_specifications',
        'is_active',
    ];

    protected $casts = [
        'facing_direction' => FacingDirection::class,
        'physical_specifications' => PhysicalSpecification::class,
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function pricings(): HasMany
    {
        return $this->hasMany(InventoryPricing::class, 'inventory_face_id');
    }

    public function pricing(): HasMany
    {
        return $this->pricings();
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(InventoryAvailability::class, 'inventory_face_id');
    }
}
