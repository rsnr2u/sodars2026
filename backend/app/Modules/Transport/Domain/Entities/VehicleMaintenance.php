<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Entities;

use App\Core\Traits\HasUuid;
use App\Platform\Identity\Infrastructure\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMaintenance extends Model
{
    use HasUuid;
    use BelongsToOrganization;

    protected $table = 'vehicle_maintenances';

    protected $fillable = [
        'organization_id',
        'vehicle_id',
        'maintenance_type',
        'description',
        'cost_cents',
        'maintenance_date',
        'odometer_reading',
        'status',
        'next_due_date',
        'next_due_odometer',
    ];

    protected $casts = [
        'cost_cents' => 'integer',
        'maintenance_date' => 'date',
        'odometer_reading' => 'integer',
        'next_due_date' => 'date',
        'next_due_odometer' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
