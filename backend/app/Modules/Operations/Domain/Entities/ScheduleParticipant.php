<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleParticipant extends BaseBusinessModel
{
    protected $table = 'operations_schedule_participants';

    protected $fillable = [
        'organization_id',
        'schedule_id',
        'participant_type',
        'participant_id',
        'role',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
}
