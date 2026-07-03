<?php

declare(strict_types=1);

namespace App\Core\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot the trait to generate UUID on creation.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(static function ($model): void {
            if (empty($model->getKey())) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Get value indicating whether primary key is incrementing.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the primary key type.
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
