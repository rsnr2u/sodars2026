<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingActivity extends BaseModel
{
    protected $table = 'booking_activities';

    protected $fillable = [
        'booking_id',
        'performed_by',
        'event_name',
        'action',
        'old_values',
        'new_values',
        'ip',
        'user_agent',
        'trace_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
