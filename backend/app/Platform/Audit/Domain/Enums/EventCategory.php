<?php

declare(strict_types=1);

namespace App\Platform\Audit\Domain\Enums;

enum EventCategory: string
{
    case Authentication = 'authentication';
    case Authorization = 'authorization';
    case DataChange = 'data_change';
    case Workflow = 'workflow';
    case Integration = 'integration';
    case Reporting = 'reporting';
    case Financial = 'financial';
    case Business = 'business';
    case Security = 'security';
    case System = 'system';
}
