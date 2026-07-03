<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Entities;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderSettlementItem extends BaseModel
{
    protected $table = 'provider_settlement_items';

    protected $fillable = [
        'id',
        'provider_settlement_id',
        'booking_item_id',
        'amount_cents',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
    ];

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(ProviderSettlement::class, 'provider_settlement_id');
    }
}
