<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\Campaigns\Domain\Enums\CreativeStatus;
use App\Platform\DAM\Domain\Entities\Asset;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignCreative extends BaseBusinessModel
{
    protected $table = 'campaign_creatives';

    protected $fillable = [
        'organization_id',
        'campaign_id',
        'asset_id',
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

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
