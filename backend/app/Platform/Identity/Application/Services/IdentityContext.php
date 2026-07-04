<?php

declare(strict_types=1);

namespace App\Platform\Identity\Application\Services;

use App\Models\User;
use App\Platform\Identity\Domain\Entities\OrganizationMember;
use Illuminate\Support\Facades\Auth;

/**
 * IdentityContext — a request-scoped context container.
 *
 * All state is stored on the container singleton instance, automatically
 * resetting between requests, tests, and queue jobs.
 */
class IdentityContext
{
    protected ?string $organizationId = null;
    protected ?string $branchId = null;
    protected ?string $userId = null;
    protected ?User $user = null;

    /**
     * Instance methods for setting and getting state.
     */
    public function performInitFromAuth(): void
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            $this->user = $user;
            $this->userId = (string) $user->id;
            $this->branchId = $user->branch_id;
            $this->organizationId = $user->organization_id;

            if (!$this->organizationId) {
                $membership = OrganizationMember::where('user_id', $this->userId)
                    ->where('status', 'active')
                    ->first();
                if ($membership) {
                    $this->organizationId = $membership->organization_id;
                }
            }
        }
    }

    public function performSetContext(?string $userId, ?string $organizationId = null, ?string $branchId = null): void
    {
        $this->userId = $userId;
        $this->organizationId = $organizationId;
        $this->branchId = $branchId;
        $this->user = $userId ? User::find($userId) : null;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getOrganizationId(): ?string
    {
        return $this->organizationId;
    }

    public function getBranchId(): ?string
    {
        return $this->branchId;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function performClear(): void
    {
        $this->organizationId = null;
        $this->branchId = null;
        $this->userId = null;
        $this->user = null;
    }

    // ─────────────────────────────────────────────────────
    // Static Proxies (Delegates to Container Singleton)
    // ─────────────────────────────────────────────────────

    public static function initFromAuth(): void
    {
        app(self::class)->performInitFromAuth();
    }

    public static function setContext(?string $userId, ?string $organizationId = null, ?string $branchId = null): void
    {
        app(self::class)->performSetContext($userId, $organizationId, $branchId);
    }

    public static function userId(): ?string
    {
        return app(self::class)->getUserId();
    }

    public static function organizationId(): ?string
    {
        return app(self::class)->getOrganizationId();
    }

    public static function branchId(): ?string
    {
        return app(self::class)->getBranchId();
    }

    public static function user(): ?User
    {
        return app(self::class)->getUser();
    }

    public static function clear(): void
    {
        app(self::class)->performClear();
    }
}
