<?php

declare(strict_types=1);

namespace App\Platform\Automation\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationRule extends Model
{
    use HasUuid;

    protected $table = 'automation_rules';

    protected $fillable = [
        'name',
        'key',
        'version',
        'event_class',
        'conditions',
        'actions',
        'is_active',
    ];

    protected $casts = [
        'version' => 'integer',
        'conditions' => 'array',
        'actions' => 'array',
        'is_active' => 'boolean',
    ];

    public function executions(): HasMany
    {
        return $this->hasMany(AutomationExecution::class, 'rule_id');
    }
}
