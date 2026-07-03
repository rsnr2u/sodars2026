<?php

declare(strict_types=1);

namespace App\Platform\DAM\Domain\Enums;

enum AssetStatus: string
{
    case UPLOADING = 'uploading';
    case PROCESSING = 'processing';
    case READY = 'ready';
    case ARCHIVED = 'archived';
    case DELETED = 'deleted';
    case FAILED = 'failed';
}
