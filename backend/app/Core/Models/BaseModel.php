<?php

declare(strict_types=1);

namespace App\Core\Models;

use App\Core\Traits\HasBranch;
use App\Core\Traits\HasCompany;
use App\Core\Traits\HasCreatedUpdatedBy;
use App\Core\Traits\HasFilters;
use App\Core\Traits\HasLogs;
use App\Core\Traits\HasMedia;
use App\Core\Traits\HasSearch;
use App\Core\Traits\HasSettings;
use App\Core\Traits\HasStatus;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseModel extends Model
{
    use HasBranch;
    use HasCompany;
    use HasCreatedUpdatedBy;
    use HasFilters;
    use HasLogs;
    use HasMedia;
    use HasSearch;
    use HasSettings;
    use HasStatus;
    use HasUuid;
    use SoftDeletes;

    /**
     * Disable auto-incrementing integer IDs.
     */
    public $incrementing = false;

    /**
     * Set key type to string.
     */
    protected $keyType = 'string';
}
