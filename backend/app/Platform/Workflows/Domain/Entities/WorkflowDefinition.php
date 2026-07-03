<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowDefinition extends Model
{
    use HasUuid;

    protected $table = 'workflow_definitions';

    protected $fillable = [
        'name',
        'key',
        'version',
        'entity_type',
        'is_active',
    ];

    protected $casts = [
        'version' => 'integer',
        'is_active' => 'boolean',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowDefinitionStep::class, 'definition_id')->orderBy('order', 'asc');
    }
}
