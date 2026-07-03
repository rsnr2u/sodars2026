<?php

declare(strict_types=1);

namespace App\Platform\Settings\Domain\Entities;

use App\Core\Models\BaseModel;

class FeatureFlag extends BaseModel
{
    protected $table = 'feature_flags';

    protected $fillable = [
        'flag_key',
        'is_enabled',
        'description',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];
}
