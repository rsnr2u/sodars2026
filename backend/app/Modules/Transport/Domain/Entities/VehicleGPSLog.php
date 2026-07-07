<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Entities;

use App\Core\Traits\HasUuid;
use App\Platform\Identity\Infrastructure\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleGPSLog extends Model
{
    use HasUuid;
    use BelongsToOrganization;

    protected $table = 'vehicle_gps_logs';

    // Telemetry logs are usually read-only and don't need update/delete times
    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'vehicle_id',
        'latitude',
        'longitude',
        'speed_kmh',
        'heading',
        'altitude',
        'accuracy',
        'engine_status',
        'ignition_status',
        'battery_voltage',
        'satellite_count',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'speed_kmh' => 'float',
        'heading' => 'float',
        'altitude' => 'float',
        'accuracy' => 'float',
        'battery_voltage' => 'float',
        'satellite_count' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
