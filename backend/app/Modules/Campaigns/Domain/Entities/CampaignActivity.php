<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignActivity extends BaseModel
{
    protected $table = 'campaign_activities';

    protected $fillable = [
        'campaign_id',
        'performed_by',
        'event_name',
        'action',
        'old_values',
        'new_values',
        'ip',
        'user_agent',
        'trace_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
