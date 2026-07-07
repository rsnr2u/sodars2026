<?php

declare(strict_types=1);

namespace App\Platform\Scheduler\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ScheduledJob extends Model
{
    use HasUuid;

    protected $table = 'scheduled_jobs';

    protected $fillable = [
        'organization_id',
        'category',
        'job_type',
        'aggregate_type',
        'aggregate_id',
        'execute_at',
        'status',
        'payload',
        'attempts',
        'retry_policy',
        'last_error',
        'triggered_at',
        'cancelled_at',
        'correlation_id',
        'trace_id',
    ];

    protected $casts = [
        'execute_at' => 'datetime',
        'payload' => 'array',
        'retry_policy' => 'array',
        'triggered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'attempts' => 'integer',
    ];
}
