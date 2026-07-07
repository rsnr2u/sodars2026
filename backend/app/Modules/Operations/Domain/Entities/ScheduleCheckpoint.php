<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleCheckpoint extends BaseBusinessModel
{
    protected $table = 'operations_schedule_checkpoints';

    protected $fillable = [
        'organization_id',
        'schedule_id',
        'name',
        'sequence',
        'status',
        'latitude',
        'longitude',
        'reached_at',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'reached_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
}
