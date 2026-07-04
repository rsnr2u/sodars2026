<?php

declare(strict_types=1);

namespace App\Platform\Identity\Infrastructure\Traits;

use App\Platform\Identity\Application\Services\IdentityContext;
use Illuminate\Database\Eloquent\Builder;

/**
 * Apply to any model requiring organization-level tenant scoping.
 */
trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope('organization', function (Builder $builder) {
            $orgId = IdentityContext::organizationId();
            if ($orgId) {
                $builder->where($builder->getModel()->getTable() . '.organization_id', $orgId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->organization_id)) {
                $model->organization_id = IdentityContext::organizationId();
            }
        });
    }
}
