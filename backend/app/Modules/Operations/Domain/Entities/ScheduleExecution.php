<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\Operations\Domain\Enums\ScheduleStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleExecution extends BaseBusinessModel
{
    protected $table = 'operations_schedule_executions';

    protected $fillable = [
        'organization_id',
        'schedule_id',
        'execution_status',
        'actual_start_time',
        'actual_end_time',
        'current_eta',
        'actual_duration_seconds',
        'actual_distance_meters',
        'execution_metrics',
    ];

    protected $casts = [
        'execution_status' => ScheduleStatus::class,
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'current_eta' => 'datetime',
        'actual_duration_seconds' => 'integer',
        'actual_distance_meters' => 'float',
        'execution_metrics' => 'array',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
}
