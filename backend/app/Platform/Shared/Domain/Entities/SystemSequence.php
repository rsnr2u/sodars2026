<?php

declare(strict_types=1);

namespace App\Platform\Shared\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class SystemSequence extends Model
{
    use HasUuid;

    protected $table = 'system_sequences';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'current_value',
    ];
}
