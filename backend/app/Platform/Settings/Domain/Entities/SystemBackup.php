<?php

declare(strict_types=1);

namespace App\Platform\Settings\Domain\Entities;

use App\Core\Models\BaseModel;

class SystemBackup extends BaseModel
{
    protected $table = 'system_backups';

    protected $fillable = [
        'file_name',
        'file_path',
        'file_size_bytes',
    ];
}
