<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Entities;

use App\Core\Traits\HasUuid;
use App\Platform\Identity\Infrastructure\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleFuelLog extends Model
{
    use HasUuid;
    use BelongsToOrganization;

    protected $table = 'vehicle_fuel_logs';

    protected $fillable = [
        'organization_id',
        'vehicle_id',
        'fuel_date',
        'liters',
        'cost_cents',
        'odometer_reading',
        'fuel_station',
        'payment_method',
        'filled_by',
        'receipt_number',
    ];

    protected $casts = [
        'fuel_date' => 'date',
        'liters' => 'float',
        'cost_cents' => 'integer',
        'odometer_reading' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
