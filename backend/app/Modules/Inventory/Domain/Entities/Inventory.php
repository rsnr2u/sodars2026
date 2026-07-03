<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Inventory\Domain\Enums\InventoryStatus;
use App\Modules\Inventory\Domain\Enums\OwnershipType;
use App\Modules\Inventory\Domain\ValueObjects\InventoryCapabilities;
use App\Modules\Inventory\Domain\ValueObjects\InventoryScore;
use App\Platform\Shared\Domain\Entities\Country;
use App\Platform\Shared\Domain\Entities\State;
use App\Platform\Shared\Domain\Entities\District;
use App\Platform\Shared\Domain\Entities\City;
use App\Platform\Shared\Domain\Entities\Pincode;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Providers\Domain\Entities\Provider;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Platform\Search\Domain\Contracts\Searchable;

class Inventory extends BaseModel implements Searchable
{
    protected $table = 'inventories';

    protected $fillable = [
        'inventory_code',
        'display_name',
        'provider_id',
        'branch_id',
        'country_id',
        'state_id',
        'district_id',
        'city_id',
        'pincode_id',
        'inventory_category',
        'inventory_type',
        'ownership_type',
        'latitude',
        'longitude',
        'geo_hash',
        'normalized_address',
        'search_keywords',
        'search_vector',
        'status',
        'marketplace_enabled',
        'is_featured',
        'accepts_programmatic_booking',
        'visibility',
        'ai_scores',
        'inventory_capabilities',
        'last_ai_analysis_at',
    ];

    protected $casts = [
        'status' => InventoryStatus::class,
        'ownership_type' => OwnershipType::class,
        'marketplace_enabled' => 'boolean',
        'is_featured' => 'boolean',
        'accepts_programmatic_booking' => 'boolean',
        'ai_scores' => InventoryScore::class,
        'inventory_capabilities' => InventoryCapabilities::class,
        'last_ai_analysis_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function pincode(): BelongsTo
    {
        return $this->belongsTo(Pincode::class, 'pincode_id');
    }

    public function faces(): HasMany
    {
        return $this->hasMany(InventoryFace::class, 'inventory_id');
    }

    public function inventoryMedia(): HasMany
    {
        return $this->hasMany(InventoryMedia::class, 'inventory_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(InventoryDocument::class, 'inventory_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(InventoryActivity::class, 'inventory_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(
            InventoryTag::class,
            'taggable',
            'inventory_taggables',
            'taggable_id',
            'tag_id'
        );
    }

    public function toSearchDocument(): array
    {
        return [
            'searchable_text' => implode(' ', array_filter([
                $this->inventory_code,
                $this->display_name,
                $this->normalized_address,
                $this->search_keywords,
            ])),
            'filterable_attributes' => [
                'status' => $this->status instanceof InventoryStatus ? $this->status->value : $this->status,
                'inventory_category' => $this->inventory_category,
                'inventory_type' => $this->inventory_type,
                'provider_id' => $this->provider_id,
                'branch_id' => $this->branch_id,
                'city_id' => $this->city_id,
                'ownership_type' => $this->ownership_type instanceof OwnershipType ? $this->ownership_type->value : $this->ownership_type,
                'marketplace_enabled' => (bool) $this->marketplace_enabled,
                'is_featured' => (bool) $this->is_featured,
            ],
            'facet_values' => [
                'status' => $this->status instanceof InventoryStatus ? $this->status->value : $this->status,
                'inventory_category' => $this->inventory_category,
                'inventory_type' => $this->inventory_type,
                'ownership_type' => $this->ownership_type instanceof OwnershipType ? $this->ownership_type->value : $this->ownership_type,
            ],
            'sortable_attributes' => [
                'created_at' => $this->created_at?->toIso8601String(),
                'display_name' => $this->display_name,
            ],
            'display_data' => [
                'name' => $this->display_name,
                'code' => $this->inventory_code,
                'address' => $this->normalized_address,
                'status' => $this->status instanceof InventoryStatus ? $this->status->value : $this->status,
            ],
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'inventories';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'display_name' => 'text',
            'inventory_code' => 'string',
            'normalized_address' => 'text',
            'search_keywords' => 'text',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status', 'inventory_category', 'inventory_type', 'ownership_type'];
    }
}
