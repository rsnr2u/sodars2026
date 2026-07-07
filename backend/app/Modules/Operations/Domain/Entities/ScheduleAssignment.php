<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleAssignment extends BaseBusinessModel
{
    protected $table = 'operations_schedule_assignments';

    protected $fillable = [
        'organization_id',
        'schedule_id',
        'resource_id',
        'assigned_at',
        'released_at',
        'released_reason',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(OperationalResource::class, 'resource_id');
    }
}
