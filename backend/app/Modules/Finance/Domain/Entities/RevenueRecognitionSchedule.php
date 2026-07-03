<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Entities\BookingItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RevenueRecognitionSchedule extends BaseModel
{
    protected $table = 'revenue_recognition_schedules';

    protected $fillable = [
        'id',
        'booking_id',
        'booking_item_id',
        'recognition_date',
        'amount_cents',
        'status',
    ];

    protected $casts = [
        'recognition_date' => 'date',
        'amount_cents' => 'integer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function bookingItem(): BelongsTo
    {
        return $this->belongsTo(BookingItem::class, 'booking_item_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(RevenueRecognitionEntry::class, 'schedule_id');
    }
}
