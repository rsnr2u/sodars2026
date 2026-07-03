<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Entities;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDocument extends BaseModel
{
    protected $table = 'booking_documents';

    protected $fillable = [
        'booking_id',
        'doc_type',
        'file_path',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
