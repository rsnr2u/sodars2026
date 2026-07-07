<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Models\User;
use App\Modules\Transport\Domain\Enums\DriverStatus;
use App\Platform\Search\Domain\Contracts\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends BaseBusinessModel implements Searchable
{
    protected $table = 'drivers';

    protected $fillable = [
        'organization_id',
        'driver_number',
        'user_id',
        'first_name',
        'last_name',
        'license_number',
        'license_class',
        'license_expiry',
        'medical_expiry',
        'badge_number',
        'employment_status',
        'joining_date',
        'emergency_contact',
        'emergency_phone',
        'background_check_date',
        'background_check_expiry',
        'training_completed',
        'training_expiry',
        'status',
    ];

    protected $casts = [
        'status' => DriverStatus::class,
        'license_expiry' => 'date',
        'medical_expiry' => 'date',
        'joining_date' => 'date',
        'background_check_date' => 'date',
        'background_check_expiry' => 'date',
        'training_completed' => 'boolean',
        'training_expiry' => 'date',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(VehicleAssignment::class, 'driver_id');
    }

    // ─── Searchable Implementation ────────────────────────────────

    public function toSearchDocument(): array
    {
        return [
            'searchable_text' => implode(' ', array_filter([
                $this->driver_number,
                $this->first_name,
                $this->last_name,
                $this->license_number,
                $this->badge_number,
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
                'driver_number' => $this->driver_number,
                'name' => $this->first_name . ' ' . $this->last_name,
                'license_number' => $this->license_number,
                'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            ],
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'transport_drivers';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'driver_number' => 'string',
            'license_number' => 'string',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status'];
    }
}
