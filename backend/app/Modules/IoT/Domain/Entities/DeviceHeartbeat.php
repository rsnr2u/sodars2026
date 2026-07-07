<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceHeartbeat extends BaseBusinessModel
{
    protected $table = 'device_heartbeats';

    protected $fillable = [
        'organization_id',
        'device_id',
        'received_at',
        'ip_address',
        'firmware_version',
        'signal_quality_dbm',
        'battery_level_percent',
        'uptime_seconds',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'signal_quality_dbm' => 'integer',
        'battery_level_percent' => 'integer',
        'uptime_seconds' => 'integer',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
