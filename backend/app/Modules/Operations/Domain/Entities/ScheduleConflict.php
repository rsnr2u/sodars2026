<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\Operations\Domain\Enums\ConflictType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleConflict extends BaseBusinessModel
{
    protected $table = 'operations_schedule_conflicts';

    protected $fillable = [
        'organization_id',
        'schedule_id',
        'conflict_type',
        'severity',
        'message',
        'detected_at',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'conflict_type' => ConflictType::class,
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
}
