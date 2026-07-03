<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Entities;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryDocument extends BaseModel
{
    protected $table = 'inventory_documents';

    protected $fillable = [
        'inventory_id',
        'document_type',
        'status',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
