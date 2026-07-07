<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Enums;

enum CommandStatus: string
{
    case Draft = 'Draft';
    case Queued = 'Queued';
    case Dispatched = 'Dispatched';
    case Delivered = 'Delivered';
    case Acknowledged = 'Acknowledged';
    case Completed = 'Completed';
    case Cancelled = 'Cancelled';
    case Failed = 'Failed';
    case Expired = 'Expired';
}
