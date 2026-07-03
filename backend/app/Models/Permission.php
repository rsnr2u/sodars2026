<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Traits\HasUuid;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasUuid;

    /**
     * Disable auto-incrementing integer IDs.
     */
    public $incrementing = false;

    /**
     * Set key type to string.
     */
    protected $keyType = 'string';
}
