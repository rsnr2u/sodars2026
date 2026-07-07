<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowExecutionToken extends Model
{
    use HasUuid;

    protected $table = 'workflow_execution_tokens';

    public $timestamps = false;

    protected $fillable = [
        'workflow_instance_id',
        'gateway_id',
        'branch_name',
        'status',
        'created_at',
        'completed_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }
}
