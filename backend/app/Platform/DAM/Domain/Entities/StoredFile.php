<?php

declare(strict_types=1);

namespace App\Platform\DAM\Domain\Entities;

use App\Core\Models\BaseModel;

class StoredFile extends BaseModel
{
    protected $table = 'dam_files';

    protected $fillable = [
        'storage_provider',
        'disk',
        'path',
        'checksum_sha256',
        'checksum_md5',
        'mime_type',
        'file_size',
        'width',
        'height',
        'duration',
        'pages',
        'dpi',
        'orientation',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration' => 'integer',
        'pages' => 'integer',
        'dpi' => 'integer',
    ];
}
