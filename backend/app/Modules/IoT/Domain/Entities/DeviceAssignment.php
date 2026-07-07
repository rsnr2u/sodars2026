<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceAssignment extends BaseBusinessModel
{
    protected $table = 'device_assignments';

    protected $fillable = [
        'organization_id',
        'device_id',
        'assignable_type',
        'assignable_id',
        'assigned_at',
        'released_at',
        'released_reason',
        'assigned_by',
        'released_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
