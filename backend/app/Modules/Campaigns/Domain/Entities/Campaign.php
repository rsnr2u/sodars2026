<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Campaign extends BaseModel
{
    protected $table = 'campaigns';

    protected $fillable = [
        'booking_id',
        'customer_id',
        'branch_id',
        'campaign_code',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'objectives',
        'budget_cents',
        'currency',
    ];

    protected $casts = [
        'status' => CampaignStatus::class,
        'objectives' => 'array',
        'budget_cents' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function inventoryFaces(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\Inventory\Domain\Entities\InventoryFace::class,
            'campaign_inventory',
            'campaign_id',
            'inventory_face_id'
        )->withTimestamps();
    }

    public function creatives(): HasMany
    {
        return $this->hasMany(CampaignCreative::class, 'campaign_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(CampaignSchedule::class, 'campaign_id');
    }

    public function proofs(): HasMany
    {
        return $this->hasMany(CampaignProof::class, 'campaign_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CampaignNote::class, 'campaign_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CampaignActivity::class, 'campaign_id');
    }
}
