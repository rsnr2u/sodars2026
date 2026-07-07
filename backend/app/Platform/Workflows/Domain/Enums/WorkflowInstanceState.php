<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Enums;

enum WorkflowInstanceState: string
{
    case Draft = 'draft';
    case Running = 'running';
    case Waiting = 'waiting';
    case Suspended = 'suspended';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Compensating = 'compensating';
    case Compensated = 'compensated';
}
