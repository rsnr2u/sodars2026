<?php

declare(strict_types=1);

namespace App\Platform\Shared\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class TemporaryFile extends Model
{
    use HasUuid;

    protected $table = 'temporary_files';

    public $incrementing = false;

    protected $keyType = 'string';

    public const UPDATED_AT = null;

    protected $fillable = [
        'file_path',
        'expiry_at',
    ];

    protected $casts = [
        'expiry_at' => 'datetime',
    ];
}
