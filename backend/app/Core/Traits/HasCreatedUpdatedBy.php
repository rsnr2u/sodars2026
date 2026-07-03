<?php

declare(strict_types=1);

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Trait HasCreatedUpdatedBy
 */
trait HasCreatedUpdatedBy
{
    /**
     * Boot the trait to set user modifiers.
     */
    protected static function bootHasCreatedUpdatedBy(): void
    {
        static::creating(static function ($model): void {
            if ($model instanceof Model && Auth::check()) {
                $userId = Auth::id();
                if (empty($model->getAttribute('created_by'))) {
                    $model->setAttribute('created_by', $userId);
                }
                if (empty($model->getAttribute('updated_by'))) {
                    $model->setAttribute('updated_by', $userId);
                }
            }
        });

        static::updating(static function ($model): void {
            if ($model instanceof Model && Auth::check()) {
                $model->setAttribute('updated_by', Auth::id());
            }
        });

        static::deleting(static function ($model): void {
            if ($model instanceof Model && Auth::check() && method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                $model->setAttribute('deleted_by', Auth::id());
                $model->save();
            }
        });
    }
}
