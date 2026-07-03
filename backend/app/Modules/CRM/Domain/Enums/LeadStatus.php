<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Enums;

enum LeadStatus: string
{
    case NEW = 'new';
    case CONTACTED = 'contacted';
    case QUALIFIED = 'qualified';
    case LOST = 'lost';
}
