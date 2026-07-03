<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Entities;

use App\Core\Traits\HasUuid;
use App\Platform\Workflows\Domain\Enums\ApprovalMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowDefinitionStep extends Model
{
    use HasUuid;

    protected $table = 'workflow_definition_steps';

    protected $fillable = [
        'definition_id',
        'name',
        'role',
        'order',
        'sla_hours',
        'approval_mode',
        'step_type',
    ];

    protected $casts = [
        'order' => 'integer',
        'sla_hours' => 'integer',
        'approval_mode' => ApprovalMode::class,
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'definition_id');
    }
}
