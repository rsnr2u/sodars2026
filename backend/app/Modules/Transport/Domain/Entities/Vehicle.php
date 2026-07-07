<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\Transport\Domain\Enums\VehicleStatus;
use App\Platform\Search\Domain\Contracts\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends BaseBusinessModel implements Searchable
{
    protected $table = 'vehicles';

    protected $fillable = [
        'organization_id',
        'fleet_id',
        'vehicle_number',
        'license_plate',
        'make',
        'model',
        'year',
        'status',
        'current_odometer',
        'payload_capacity',
        'volume_capacity',
        'number_of_screens',
        'max_billboards',
        'predicted_failure_probability',
        'maintenance_risk_score',
        'fuel_efficiency_score',
        'vehicle_health_score',
        'predicted_maintenance_date',
        'predicted_fuel_cost',
    ];

    protected $casts = [
        'status' => VehicleStatus::class,
        'year' => 'integer',
        'current_odometer' => 'integer',
        'payload_capacity' => 'float',
        'volume_capacity' => 'float',
        'number_of_screens' => 'integer',
        'max_billboards' => 'integer',
        'predicted_failure_probability' => 'float',
        'maintenance_risk_score' => 'float',
        'fuel_efficiency_score' => 'float',
        'vehicle_health_score' => 'float',
        'predicted_maintenance_date' => 'date',
        'predicted_fuel_cost' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class, 'fleet_id');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(VehicleMaintenance::class, 'vehicle_id');
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(VehicleFuelLog::class, 'vehicle_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(VehicleAssignment::class, 'vehicle_id');
    }

    // ─── Searchable Implementation ────────────────────────────────

    public function toSearchDocument(): array
    {
        return [
            'searchable_text' => implode(' ', array_filter([
                $this->vehicle_number,
                $this->license_plate,
                $this->make,
                $this->model,
                (string) $this->year,
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
                'vehicle_number' => $this->vehicle_number,
                'license_plate' => $this->license_plate,
                'make' => $this->make,
                'model' => $this->model,
                'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            ],
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'transport_vehicles';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'vehicle_number' => 'string',
            'license_plate' => 'string',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status'];
    }
}
