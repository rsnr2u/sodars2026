<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends BaseModel
{
    protected $table = 'booking_items';

    protected $fillable = [
        'booking_id',
        'inventory_face_id',
        'start_date',
        'end_date',
        'daily_frequency',
        'net_price_cents',
        'markup_percentage',
        'retail_price_cents',
        'total_item_price_cents',
        'pricing_snapshot',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'daily_frequency' => 'integer',
        'net_price_cents' => 'integer',
        'markup_percentage' => 'integer',
        'retail_price_cents' => 'integer',
        'total_item_price_cents' => 'integer',
        'pricing_snapshot' => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function face(): BelongsTo
    {
        return $this->belongsTo(InventoryFace::class, 'inventory_face_id');
    }
}
