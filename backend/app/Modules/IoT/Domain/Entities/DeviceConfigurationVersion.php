<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceConfigurationVersion extends BaseBusinessModel
{
    protected $table = 'device_configuration_versions';

    protected $fillable = [
        'organization_id',
        'device_id',
        'version',
        'configuration',
        'created_by',
    ];

    protected $casts = [
        'configuration' => 'array',
        'version' => 'integer',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
