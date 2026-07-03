<?php

declare(strict_types=1);

namespace App\Platform\Shared\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasUuid;

    protected $table = 'activity_logs';

    public $incrementing = false;

    protected $keyType = 'string';

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'log_message',
        'ip_address',
    ];
}
