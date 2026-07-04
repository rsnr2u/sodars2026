<?php

declare(strict_types=1);

namespace App\Platform\Identity\Application\Services;

use App\Platform\Identity\Domain\Entities\LoginSession;
use App\Platform\Identity\Domain\ValueObjects\DeviceFingerprint;
use Illuminate\Support\Str;

class SessionService
{
    /**
     * Create a new login session record.
     */
    public function createSession(string $userId, string $ipAddress, string $userAgent): LoginSession
    {
        $fingerprint = DeviceFingerprint::fromUserAgent($userAgent);

        return LoginSession::create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_type' => $fingerprint->deviceType,
            'browser' => $fingerprint->browser,
            'os' => $fingerprint->os,
            'location' => null, // Future: Geolocate IP
            'logged_in_at' => now(),
            'last_active_at' => now(),
            'is_revoked' => false,
        ]);
    }

    /**
     * List active, non-revoked sessions for a user.
     */
    public function getActiveSessions(string $userId): \Illuminate\Database\Eloquent\Collection
    {
        return LoginSession::where('user_id', $userId)
            ->where('is_revoked', false)
            ->whereNull('logged_out_at')
            ->orderBy('last_active_at', 'desc')
            ->get();
    }

    /**
     * Revoke a specific session.
     */
    public function revokeSession(string $sessionId): bool
    {
        $session = LoginSession::find($sessionId);
        if ($session) {
            $session->update([
                'is_revoked' => true,
                'logged_out_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    /**
     * Revoke all active sessions for a user except the current one.
     */
    public function revokeOtherSessions(string $userId, string $currentSessionId): int
    {
        return LoginSession::where('user_id', $userId)
            ->where('id', '!=', $currentSessionId)
            ->where('is_revoked', false)
            ->update([
                'is_revoked' => true,
                'logged_out_at' => now(),
            ]);
    }

    /**
     * Update the last active timestamp of a session.
     */
    public function pingSession(string $sessionId): void
    {
        LoginSession::where('id', $sessionId)
            ->update(['last_active_at' => now()]);
    }
}
