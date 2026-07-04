<?php

declare(strict_types=1);

namespace App\Platform\Identity\Infrastructure\Security;

use App\Models\User;
use App\Platform\Identity\Domain\Contracts\PermissionResolver;

class SpatiePermissionResolver implements PermissionResolver
{
    public function hasPermission(string $userId, string $permission): bool
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        // Super Admin bypass
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }

    public function getUserPermissions(string $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return [];
        }

        return $user->getAllPermissions()->pluck('name')->toArray();
    }

    public function getUserRoles(string $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return [];
        }

        return $user->getRoleNames()->toArray();
    }
}
