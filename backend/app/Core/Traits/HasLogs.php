<?php

declare(strict_types=1);

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasLogs
{
    /**
     * Get all of the model's audit logs.
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany('App\Platform\Shared\Domain\Entities\AuditLog', 'auditable');
    }
}
