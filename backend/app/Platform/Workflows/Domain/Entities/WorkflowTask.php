<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Entities;

use App\Core\Traits\HasUuid;
use App\Platform\Workflows\Domain\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTask extends Model
{
    use HasUuid;

    protected $table = 'workflow_tasks';

    protected $fillable = [
        'instance_id',
        'step_id',
        'status',
        'assigned_role',
        'assigned_user_id',
        'actioned_by',
        'actioned_at',
        'comments',
        'due_at',
        'escalated_to_role',
        'escalated_at',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'assigned_user_id' => 'string',
        'actioned_by' => 'string',
        'actioned_at' => 'datetime',
        'due_at' => 'datetime',
        'escalated_at' => 'datetime',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'instance_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinitionStep::class, 'step_id');
    }
}
