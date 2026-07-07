<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceWorkloadProjection extends BaseBusinessModel
{
    protected $table = 'operations_resource_workload_projections';

    protected $fillable = [
        'organization_id',
        'resource_id',
        'assigned_schedules_count',
        'total_allocated_seconds',
        'utilization_score',
    ];

    protected $casts = [
        'assigned_schedules_count' => 'integer',
        'total_allocated_seconds' => 'integer',
        'utilization_score' => 'integer',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(OperationalResource::class, 'resource_id');
    }
}
