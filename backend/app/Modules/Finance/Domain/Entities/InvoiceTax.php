<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Entities;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceTax extends BaseModel
{
    protected $table = 'invoice_taxes';

    protected $fillable = [
        'id',
        'invoice_id',
        'tax_name',
        'tax_rate_percentage',
        'tax_amount_cents',
    ];

    protected $casts = [
        'tax_rate_percentage' => 'float',
        'tax_amount_cents' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
