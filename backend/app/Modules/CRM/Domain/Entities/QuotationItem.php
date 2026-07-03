<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends BaseModel
{
    protected $table = 'crm_quotation_items';

    protected $fillable = [
        'quotation_version_id',
        'inventory_face_id',
        'start_date',
        'end_date',
        'daily_frequency',
        'price_cents',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'daily_frequency' => 'integer',
        'price_cents' => 'integer',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(QuotationVersion::class, 'quotation_version_id');
    }

    public function face(): BelongsTo
    {
        return $this->belongsTo(InventoryFace::class, 'inventory_face_id');
    }
}
