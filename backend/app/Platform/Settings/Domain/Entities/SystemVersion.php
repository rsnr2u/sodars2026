<?php

declare(strict_types=1);

namespace App\Platform\Settings\Domain\Entities;

use App\Core\Models\BaseModel;

class SystemVersion extends BaseModel
{
    protected $table = 'system_versions';

    protected $fillable = [
        'version_tag',
        'release_notes',
        'deployed_at',
    ];

    protected $casts = [
        'deployed_at' => 'datetime',
    ];
}
