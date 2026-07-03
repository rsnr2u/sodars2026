<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Entities;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationVersion extends BaseModel
{
    protected $table = 'crm_quotation_versions';

    protected $fillable = [
        'quotation_id',
        'version_number',
        'valid_until',
        'subtotal_cents',
        'discount_cents',
        'tax_cents',
        'grand_total_cents',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'valid_until' => 'date',
        'subtotal_cents' => 'integer',
        'discount_cents' => 'integer',
        'tax_cents' => 'integer',
        'grand_total_cents' => 'integer',
        'is_active' => 'boolean',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class, 'quotation_version_id');
    }
}
