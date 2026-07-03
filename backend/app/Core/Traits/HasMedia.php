<?php

declare(strict_types=1);

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMedia
{
    /**
     * Get all of the model's media files.
     */
    public function media(): MorphMany
    {
        // For custom morph mapping to target the central media library
        return $this->morphMany('App\Platform\Shared\Domain\Entities\MediaLibrary', 'mediable');
    }
}
