<?php

declare(strict_types=1);

namespace App\Platform\Identity\Domain\Enums;

enum MembershipStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Invited = 'invited';
}
