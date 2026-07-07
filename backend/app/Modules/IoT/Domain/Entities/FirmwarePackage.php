<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FirmwarePackage extends BaseBusinessModel
{
    protected $table = 'firmware_packages';

    protected $fillable = [
        'organization_id',
        'version',
        'sha256',
        'size_bytes',
        'signature',
        'signature_algorithm',
        'download_url',
        'min_supported_version',
        'max_supported_version',
        'compatible_device_types',
        'published_at',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'compatible_device_types' => 'array',
        'published_at' => 'datetime',
    ];

    public function installations(): HasMany
    {
        return $this->hasMany(DeviceFirmwareInstallation::class, 'firmware_package_id');
    }
}
