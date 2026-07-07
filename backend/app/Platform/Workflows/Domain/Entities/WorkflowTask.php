<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Entities;

use App\Core\Traits\HasUuid;
use App\Platform\Workflows\Domain\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowTask extends Model
{
    use HasUuid;

    protected $table = 'workflow_tasks';

    protected $fillable = [
        'instance_id',
        'step_name',
        'status',
        'due_at',
        'completed_at',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'instance_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(WorkflowTaskAssignment::class, 'task_id');
    }
}
