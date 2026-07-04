<?php

declare(strict_types=1);

namespace App\Platform\Identity\Domain\Contracts;

/**
 * Permission resolution abstraction over Spatie.
 * Future: Roles + Teams + Organization + Overrides + ABAC.
 */
interface PermissionResolver
{
    public function hasPermission(string $userId, string $permission): bool;

    public function getUserPermissions(string $userId): array;

    public function getUserRoles(string $userId): array;
}
