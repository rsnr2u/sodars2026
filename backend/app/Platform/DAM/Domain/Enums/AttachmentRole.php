<?php

declare(strict_types=1);

namespace App\Platform\DAM\Domain\Enums;

enum AttachmentRole: string
{
    case PRIMARY = 'primary';
    case GALLERY = 'gallery';
    case THUMBNAIL = 'thumbnail';
    case HERO = 'hero';
    case BANNER = 'banner';
    case DOCUMENT = 'document';
    case CREATIVE = 'creative';
    case INVOICE = 'invoice';
    case PROOF = 'proof';
}
