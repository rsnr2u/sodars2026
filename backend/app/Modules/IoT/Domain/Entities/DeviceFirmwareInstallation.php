<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\IoT\Domain\Enums\FirmwareInstallationStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceFirmwareInstallation extends BaseBusinessModel
{
    protected $table = 'device_firmware_installations';

    protected $fillable = [
        'organization_id',
        'device_id',
        'firmware_package_id',
        'status',
        'started_at',
        'completed_at',
        'rollback_from',
        'rollback_to',
        'last_error',
    ];

    protected $casts = [
        'status' => FirmwareInstallationStatus::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(FirmwarePackage::class, 'firmware_package_id');
    }
}
