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
        'definition_version_id',
        'entity_id',
        'entity_type',
        'organization_id',
        'status',
        'current_state',
        'dsl_snapshot',
        'context_snapshot',
        'started_at',
        'due_at',
        'completed_at',
        'cancelled_at',
        'sla_status',
        'current_step_index',
    ];

    protected $casts = [
        'status' => WorkflowStatus::class,
        'dsl_snapshot' => 'array',
        'context_snapshot' => 'array',
        'started_at' => 'datetime',
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function definitionVersion(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinitionVersion::class, 'definition_version_id');
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

    public function variables(): HasMany
    {
        return $this->hasMany(WorkflowVariable::class, 'instance_id');
    }
}
