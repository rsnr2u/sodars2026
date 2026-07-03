<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Entities;

use App\Core\Traits\HasUuid;
use App\Platform\Workflows\Domain\Enums\WorkflowStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowInstance extends Model
{
    use HasUuid;

    protected $table = 'workflow_instances';

    protected $fillable = [
        'definition_id',
        'entity_id',
        'entity_type',
        'status',
        'current_step_index',
        'context_snapshot',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'status' => WorkflowStatus::class,
        'current_step_index' => 'integer',
        'context_snapshot' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'definition_id');
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(WorkflowTask::class, 'instance_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(WorkflowHistory::class, 'instance_id')->orderBy('created_at', 'asc');
    }
}
