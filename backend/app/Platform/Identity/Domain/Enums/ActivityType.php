<?php

declare(strict_types=1);

namespace App\Platform\Identity\Domain\Enums;

enum ActivityType: string
{
    case Login = 'login';
    case Logout = 'logout';
    case PasswordChanged = 'password_changed';
    case RoleAssigned = 'role_assigned';
    case OrganizationJoined = 'organization_joined';
    case OrganizationLeft = 'organization_left';
    case TeamJoined = 'team_joined';
    case TeamLeft = 'team_left';
    case SessionRevoked = 'session_revoked';
    case ProfileUpdated = 'profile_updated';
    case EntityCreated = 'entity_created';
    case EntityUpdated = 'entity_updated';
    case EntityDeleted = 'entity_deleted';
}
