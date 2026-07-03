<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Finance\Domain\Enums\SettlementStatus;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Bookings\Domain\Entities\Booking;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProviderSettlement extends BaseModel
{
    protected $table = 'provider_settlements';

    protected $fillable = [
        'id',
        'settlement_number',
        'provider_id',
        'booking_id',
        'invoice_id',
        'total_amount_cents',
        'provider_share_cents',
        'commission_cents',
        'tax_cents',
        'status',
        'processed_at',
        'payout_reference',
    ];

    protected $casts = [
        'total_amount_cents' => 'integer',
        'provider_share_cents' => 'integer',
        'commission_cents' => 'integer',
        'tax_cents' => 'integer',
        'processed_at' => 'datetime',
        'status' => SettlementStatus::class,
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProviderSettlementItem::class, 'provider_settlement_id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(ProviderSettlementAdjustment::class, 'provider_settlement_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProviderSettlementActivity::class, 'provider_settlement_id');
    }
}
