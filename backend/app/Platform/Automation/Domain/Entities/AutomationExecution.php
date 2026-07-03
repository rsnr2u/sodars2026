<?php

declare(strict_types=1);

namespace App\Platform\Automation\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationExecution extends Model
{
    use HasUuid;

    protected $table = 'automation_executions';

    protected $fillable = [
        'rule_id',
        'event_name',
        'context_snapshot',
        'status',
        'execution_time_ms',
        'started_at',
        'completed_at',
        'error_message',
    ];

    public $timestamps = false;

    protected $casts = [
        'context_snapshot' => 'array',
        'execution_time_ms' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class, 'rule_id');
    }
}
