<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurrenceRule extends BaseBusinessModel
{
    protected $table = 'operations_recurrence_rules';

    protected $fillable = [
        'organization_id',
        'schedule_id',
        'frequency',
        'interval',
        'by_days',
        'exception_dates',
        'ends_at',
    ];

    protected $casts = [
        'interval' => 'integer',
        'by_days' => 'array',
        'exception_dates' => 'array',
        'ends_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
}
