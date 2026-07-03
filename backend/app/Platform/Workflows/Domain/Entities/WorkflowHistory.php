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

    protected $fillable = [
        'instance_id',
        'from_status',
        'to_status',
        'action',
        'comments',
        'actioned_by',
        'created_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'instance_id');
    }
}
