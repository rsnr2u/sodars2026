<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchProgressProjection extends BaseBusinessModel
{
    protected $table = 'operations_dispatch_progress_projections';

    protected $fillable = [
        'organization_id',
        'schedule_id',
        'execution_id',
        'completed_checkpoints_count',
        'total_checkpoints_count',
        'completion_percentage',
        'eta_estimate',
    ];

    protected $casts = [
        'completed_checkpoints_count' => 'integer',
        'total_checkpoints_count' => 'integer',
        'completion_percentage' => 'integer',
        'eta_estimate' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function execution(): BelongsTo
    {
        return $this->belongsTo(ScheduleExecution::class, 'execution_id');
    }
}
