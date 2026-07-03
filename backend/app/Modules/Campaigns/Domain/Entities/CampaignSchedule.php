<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignSchedule extends BaseModel
{
    protected $table = 'campaign_schedule';

    protected $fillable = [
        'campaign_id',
        'inventory_face_id',
        'date',
        'slot_index',
    ];

    protected $casts = [
        'date' => 'date',
        'slot_index' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function face(): BelongsTo
    {
        return $this->belongsTo(InventoryFace::class, 'inventory_face_id');
    }
}
