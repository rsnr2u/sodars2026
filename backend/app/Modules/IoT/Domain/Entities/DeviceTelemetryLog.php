<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceTelemetryLog extends BaseBusinessModel
{
    protected $table = 'device_telemetry_logs';

    protected $fillable = [
        'organization_id',
        'device_id',
        'logged_at',
        'latitude',
        'longitude',
        'speed_kph',
        'heading_degrees',
        'diagnostics',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'speed_kph' => 'float',
        'heading_degrees' => 'integer',
        'diagnostics' => 'array',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
