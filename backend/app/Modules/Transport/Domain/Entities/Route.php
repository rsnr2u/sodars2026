<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\Transport\Domain\Enums\RouteStatus;
use App\Platform\Search\Domain\Contracts\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends BaseBusinessModel implements Searchable
{
    protected $table = 'routes';

    protected $fillable = [
        'organization_id',
        'route_number',
        'vehicle_id',
        'driver_id',
        'booking_id',
        'booking_item_id',
        'campaign_id',
        'inventory_reservation_id',
        'start_location',
        'end_location',
        'planned_distance_km',
        'planned_duration_minutes',
        'actual_distance_km',
        'actual_duration_minutes',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'status' => RouteStatus::class,
        'planned_distance_km' => 'float',
        'planned_duration_minutes' => 'integer',
        'actual_distance_km' => 'float',
        'actual_duration_minutes' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function stops(): HasMany
    {
        return $this->hasMany(RouteStop::class, 'route_id');
    }

    // ─── Searchable Implementation ────────────────────────────────

    public function toSearchDocument(): array
    {
        return [
            'searchable_text' => implode(' ', array_filter([
                $this->route_number,
                $this->start_location,
                $this->end_location,
                $this->vehicle?->vehicle_number,
                $this->driver?->first_name,
                $this->driver?->last_name,
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
                'route_number' => $this->route_number,
                'start_location' => $this->start_location,
                'end_location' => $this->end_location,
                'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            ],
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'transport_routes';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'route_number' => 'string',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status'];
    }
}
