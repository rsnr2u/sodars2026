<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceHealthSnapshot extends BaseBusinessModel
{
    protected $table = 'device_health_snapshots';

    protected $fillable = [
        'organization_id',
        'device_id',
        'cpu_usage_percent',
        'memory_usage_percent',
        'disk_usage_percent',
        'temperature_celsius',
        'battery_level_percent',
        'signal_quality_dbm',
        'overall_health_score',
        'battery_score',
        'signal_score',
        'temperature_score',
        'storage_score',
        'last_seen_at',
    ];

    protected $casts = [
        'cpu_usage_percent' => 'integer',
        'memory_usage_percent' => 'integer',
        'disk_usage_percent' => 'integer',
        'temperature_celsius' => 'integer',
        'battery_level_percent' => 'integer',
        'signal_quality_dbm' => 'integer',
        'overall_health_score' => 'integer',
        'battery_score' => 'integer',
        'signal_score' => 'integer',
        'temperature_score' => 'integer',
        'storage_score' => 'integer',
        'last_seen_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
