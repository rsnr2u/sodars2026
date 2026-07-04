<?php

declare(strict_types=1);

namespace App\Platform\Identity\Domain\Enums;

enum TeamRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';
    case Viewer = 'viewer';
}
