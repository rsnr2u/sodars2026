<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Domain\Enums;

enum NotificationCategory: string
{
    case TRANSACTIONAL = 'transactional';
    case MARKETING = 'marketing';
    case SYSTEM = 'system';
    case SECURITY = 'security';
    case FINANCE = 'finance';
    case CRM = 'crm';
    case CAMPAIGN = 'campaign';
}
