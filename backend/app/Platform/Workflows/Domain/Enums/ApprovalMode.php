<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Enums;

enum ApprovalMode: string
{
    case Any = 'any';
    case All = 'all';
}
