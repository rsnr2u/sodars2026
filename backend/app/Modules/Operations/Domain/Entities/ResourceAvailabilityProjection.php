<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\Operations\Domain\Enums\ResourceState;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceAvailabilityProjection extends BaseBusinessModel
{
    protected $table = 'operations_resource_availability_projections';

    protected $fillable = [
        'organization_id',
        'resource_id',
        'current_state',
        'blocked_time_slots',
        'last_updated_at',
    ];

    protected $casts = [
        'current_state' => ResourceState::class,
        'blocked_time_slots' => 'array',
        'last_updated_at' => 'datetime',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(OperationalResource::class, 'resource_id');
    }
}
