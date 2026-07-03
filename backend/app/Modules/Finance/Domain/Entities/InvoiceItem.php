<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Entities;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends BaseModel
{
    protected $table = 'invoice_items';

    protected $fillable = [
        'id',
        'invoice_id',
        'description',
        'quantity',
        'unit_price_cents',
        'total_price_cents',
        'pricing_snapshot',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_cents' => 'integer',
        'total_price_cents' => 'integer',
        'pricing_snapshot' => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
