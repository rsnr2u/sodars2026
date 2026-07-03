<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Models\User;
use App\Modules\Bookings\Domain\Enums\PaymentMethod;
use App\Modules\Bookings\Domain\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends BaseModel
{
    protected $table = 'payments';

    protected $fillable = [
        'paymentable_id',
        'paymentable_type',
        'payment_method',
        'amount_cents',
        'reference_number',
        'status',
        'recorded_by',
    ];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
        'amount_cents' => 'integer',
    ];

    public function paymentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
