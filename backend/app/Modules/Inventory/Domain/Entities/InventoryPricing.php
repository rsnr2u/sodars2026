<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Inventory\Domain\Enums\PricingType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryPricing extends BaseModel
{
    protected $table = 'inventory_pricing';

    protected $fillable = [
        'inventory_face_id',
        'pricing_type',
        'rate_cents',
        'currency',
        'tax_inclusive',
        'minimum_booking_days',
        'effective_from',
        'effective_to',
        'priority',
    ];

    protected $casts = [
        'pricing_type' => PricingType::class,
        'rate_cents' => 'integer',
        'tax_inclusive' => 'boolean',
        'minimum_booking_days' => 'integer',
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
        'priority' => 'integer',
    ];

    public function face(): BelongsTo
    {
        return $this->belongsTo(InventoryFace::class, 'inventory_face_id');
    }
}
