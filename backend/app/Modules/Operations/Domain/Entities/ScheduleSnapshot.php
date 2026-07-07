<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleSnapshot extends BaseBusinessModel
{
    protected $table = 'operations_schedule_snapshots';

    protected $fillable = [
        'organization_id',
        'schedule_id',
        'trigger_state',
        'snapshot_data',
        'captured_at',
    ];

    protected $casts = [
        'snapshot_data' => 'array',
        'captured_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
}
