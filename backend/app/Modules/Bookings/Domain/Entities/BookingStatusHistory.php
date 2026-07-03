<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStatusHistory extends BaseModel
{
    protected $table = 'booking_status_history';

    protected $fillable = [
        'booking_id',
        'changed_by',
        'from_status',
        'to_status',
        'comment',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
