<?php

declare(strict_types=1);

namespace App\Core\Models;

use App\Platform\Identity\Infrastructure\Traits\BelongsToOrganization;
use App\Platform\Audit\Infrastructure\Traits\Auditable;

abstract class BaseBusinessModel extends BaseModel
{
    use BelongsToOrganization;
    use Auditable;

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function ($model) {
            $model->beforeSave();
        });

        static::saved(function ($model) {
            $model->afterSave();
        });
    }

    protected function beforeSave(): void
    {
        // Custom business logic before model is saved
    }

    protected function afterSave(): void
    {
        // Custom business logic after model is saved
    }
}
