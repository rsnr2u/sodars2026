<?php

declare(strict_types=1);

namespace App\Platform\DAM\Domain\Enums;

enum AssetType: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case DOCUMENT = 'document';
    case ARCHIVE = 'archive';
    case OTHER = 'other';
}
