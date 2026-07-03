<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Domain\Enums;

enum NotificationStatus: string
{
    case DRAFT = 'draft';
    case QUEUED = 'queued';
    case PROCESSING = 'processing';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case READ = 'read';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
}
