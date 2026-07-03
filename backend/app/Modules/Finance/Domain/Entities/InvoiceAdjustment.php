<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Entities;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceAdjustment extends BaseModel
{
    protected $table = 'invoice_adjustments';

    protected $fillable = [
        'id',
        'invoice_id',
        'adjustment_type',
        'amount_cents',
        'reason',
        'recorded_by',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
