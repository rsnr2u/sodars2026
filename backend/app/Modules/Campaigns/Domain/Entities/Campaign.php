<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Models\User;
use App\Platform\Search\Domain\Contracts\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Campaign extends BaseBusinessModel implements Searchable
{
    protected $table = 'campaigns';

    protected $fillable = [
        'organization_id',
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
        'planned_budget_cents',
        'approved_budget_cents',
        'actual_spend_cents',
        'remaining_budget_cents',
        'currency',
    ];

    protected $casts = [
        'status' => CampaignStatus::class,
        'objectives' => 'array',
        'budget_cents' => 'integer',
        'planned_budget_cents' => 'integer',
        'approved_budget_cents' => 'integer',
        'actual_spend_cents' => 'integer',
        'remaining_budget_cents' => 'integer',
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

    // ─── Searchable Implementation ────────────────────────────────

    public function toSearchDocument(): array
    {
        return [
            'searchable_text' => implode(' ', array_filter([
                $this->name,
                $this->campaign_code,
                $this->description,
                $this->customer?->name,
                $this->customer?->email,
            ])),
            'filterable_attributes' => [
                'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
                'organization_id' => $this->organization_id,
            ],
            'facet_values' => [
                'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            ],
            'sortable_attributes' => [
                'created_at' => $this->created_at?->toIso8601String(),
            ],
            'display_data' => [
                'name' => $this->name,
                'code' => $this->campaign_code,
                'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            ],
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'campaign_campaigns';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'name' => 'string',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status'];
    }
}
