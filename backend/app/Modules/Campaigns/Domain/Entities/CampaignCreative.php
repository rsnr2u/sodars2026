<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Campaigns\Domain\Enums\CreativeStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignCreative extends BaseModel
{
    protected $table = 'campaign_creatives';

    protected $fillable = [
        'campaign_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size_bytes',
        'version',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'status' => CreativeStatus::class,
        'reviewed_at' => 'datetime',
        'file_size_bytes' => 'integer',
        'version' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }
}
