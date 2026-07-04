<?php

declare(strict_types=1);

namespace App\Platform\Identity\Application\Services;

use App\Models\User;
use App\Platform\Identity\Domain\Entities\OrganizationMember;
use Illuminate\Support\Facades\Auth;

/**
 * IdentityContext — a queue-safe, CLI-safe, API-safe identity abstraction.
 *
 * Replaces direct auth()->user() calls throughout the platform.
 * Can be manually populated for jobs, schedulers, and CLI commands.
 */
class IdentityContext
{
    protected static ?string $organizationId = null;
    protected static ?string $branchId = null;
    protected static ?string $userId = null;
    protected static ?User $user = null;

    /**
     * Initialize context from the authenticated user.
     */
    public static function initFromAuth(): void
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            self::$user = $user;
            self::$userId = (string) $user->id;
            self::$branchId = $user->branch_id;
            self::$organizationId = $user->organization_id;

            // Resolve primary org from organization_members if not set on user
            if (!self::$organizationId) {
                $membership = OrganizationMember::where('user_id', self::$userId)
                    ->where('status', 'active')
                    ->first();
                if ($membership) {
                    self::$organizationId = $membership->organization_id;
                }
            }
        }
    }

    /**
     * Manually set context (for jobs, schedulers, CLI, tests).
     */
    public static function setContext(?string $userId, ?string $organizationId = null, ?string $branchId = null): void
    {
        self::$userId = $userId;
        self::$organizationId = $organizationId;
        self::$branchId = $branchId;
        self::$user = $userId ? User::find($userId) : null;
    }

    public static function userId(): ?string
    {
        return self::$userId;
    }

    public static function organizationId(): ?string
    {
        return self::$organizationId;
    }

    public static function branchId(): ?string
    {
        return self::$branchId;
    }

    public static function user(): ?User
    {
        return self::$user;
    }

    /**
     * Clear identity context (useful for tests and request lifecycle).
     */
    public static function clear(): void
    {
        self::$organizationId = null;
        self::$branchId = null;
        self::$userId = null;
        self::$user = null;
    }
}
