<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Enums;

enum WorkflowStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Terminated = 'terminated';
}
