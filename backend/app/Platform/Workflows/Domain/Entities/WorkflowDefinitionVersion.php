<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowDefinitionVersion extends Model
{
    use HasUuid;

    protected $table = 'workflow_definition_versions';

    protected $fillable = [
        'definition_id',
        'version',
        'dsl_schema',
        'status',
        'is_active',
    ];

    protected $casts = [
        'dsl_schema' => 'array',
        'is_active' => 'boolean',
        'version' => 'integer',
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'definition_id');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class, 'definition_version_id');
    }
}
