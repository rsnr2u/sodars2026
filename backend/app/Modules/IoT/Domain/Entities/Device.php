<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\IoT\Domain\Enums\DeviceStatus;
use App\Modules\IoT\Domain\Enums\DeviceType;
use App\Platform\Search\Domain\Contracts\Searchable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Device extends BaseBusinessModel implements Searchable
{
    protected $table = 'devices';

    protected $fillable = [
        'organization_id',
        'device_number',
        'serial_number',
        'name',
        'device_type',
        'status',
        'imei',
        'iccid',
        'mac_address',
        'manufacturer',
        'hardware_revision',
        'firmware_version',
        'device_secret',
        'last_seen_at',
        'current_configuration_version_id',
    ];

    protected $casts = [
        'device_type' => DeviceType::class,
        'status' => DeviceStatus::class,
        'last_seen_at' => 'datetime',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(DeviceAssignment::class, 'device_id');
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(DeviceConfigurationVersion::class, 'device_id');
    }

    public function heartbeats(): HasMany
    {
        return $this->hasMany(DeviceHeartbeat::class, 'device_id');
    }

    public function commands(): HasMany
    {
        return $this->hasMany(DeviceCommand::class, 'device_id');
    }

    public function installations(): HasMany
    {
        return $this->hasMany(DeviceFirmwareInstallation::class, 'device_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(DeviceAlert::class, 'device_id');
    }

    public function telemetryLogs(): HasMany
    {
        return $this->hasMany(DeviceTelemetryLog::class, 'device_id');
    }

    public function healthSnapshot(): HasOne
    {
        return $this->hasOne(DeviceHealthSnapshot::class, 'device_id');
    }

    // Searchable Contract Implementation
    public function toSearchDocument(): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'title' => $this->name,
            'subtitle' => $this->device_number,
            'content' => "Serial: {$this->serial_number} | Type: {$this->device_type->value} | Status: {$this->status->value}",
            'type' => 'iot_device',
            'metadata' => [
                'device_number' => $this->device_number,
                'status' => $this->status->value,
                'device_type' => $this->device_type->value,
            ],
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'iot_devices';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'device_number' => 'string',
            'serial_number' => 'string',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status', 'device_type'];
    }
}
