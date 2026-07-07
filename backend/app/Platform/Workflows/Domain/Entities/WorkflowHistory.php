<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowHistory extends Model
{
    use HasUuid;

    protected $table = 'workflow_histories';

    // History logs are append-only and do not require Laravel updated_at timestamps
    public $timestamps = false;

    protected $fillable = [
        'instance_id',
        'task_id',
        'from_state',
        'to_state',
        'action',
        'comments',
        'actioned_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'instance_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkflowTask::class, 'task_id');
    }
}
