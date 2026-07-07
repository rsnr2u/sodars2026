<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleTimeline extends BaseBusinessModel
{
    protected $table = 'operations_schedule_timelines';

    protected $fillable = [
        'organization_id',
        'schedule_id',
        'event_name',
        'description',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
}
