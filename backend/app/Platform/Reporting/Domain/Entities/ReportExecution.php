<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Domain\Entities;

use App\Core\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportExecution extends Model
{
    use HasUuid;

    protected $table = 'report_executions';

    protected $fillable = [
        'scheduled_report_id',
        'report_key',
        'status',
        'started_at',
        'completed_at',
        'duration_ms',
        'dam_asset_id',
        'error_message',
        'executed_by',
        'context_snapshot',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_ms' => 'integer',
        'context_snapshot' => 'array',
    ];

    public function executedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function scheduledReport(): BelongsTo
    {
        return $this->belongsTo(ScheduledReport::class, 'scheduled_report_id');
    }
}
