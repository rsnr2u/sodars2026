<?php

declare(strict_types=1);

namespace App\Platform\Identity\Application\Listeners;

use App\Platform\Identity\Application\Services\IdentityContext;
use App\Platform\Identity\Application\Services\SessionService;
use App\Platform\Identity\Application\Services\ActivityService;
use App\Platform\Identity\Domain\Enums\ActivityType;
use App\Platform\Identity\Domain\Events\UserLoggedIn as DomainUserLoggedIn;
use App\Platform\Identity\Domain\Events\UserLoggedOut as DomainUserLoggedOut;
use App\Platform\Identity\Domain\Entities\LoginSession;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;

class AuthEventListener
{
    public function __construct(
        protected SessionService $sessionService
    ) {}

    /**
     * Handle user login.
     */
    public function onLogin(Login $event): void
    {
        $user = $event->user;
        $ip = request()->ip() ?? '127.0.0.1';
        $ua = request()->userAgent() ?? 'Unknown';

        // 1. Initialize context
        IdentityContext::setContext((string) $user->id, $user->organization_id, $user->branch_id);

        // 2. Create Login Session
        $session = $this->sessionService->createSession((string) $user->id, $ip, $ua);

        // 3. Store session ID in current web session if available
        if (request()->hasSession()) {
            request()->session()->put('login_session_id', $session->id);
        }

        // 4. Record Activity Log
        ActivityService::record(
            ActivityType::Login,
            "User {$user->name} logged in from IP {$ip}",
            $user
        );

        // 5. Dispatch domain event
        DomainUserLoggedIn::dispatch((string) $user->id, $ip, $ua);
    }

    /**
     * Handle user logout.
     */
    public function onLogout(Logout $event): void
    {
        $user = $event->user;
        if (!$user) {
            return;
        }

        $ip = request()->ip() ?? '127.0.0.1';

        // 1. Revoke session
        $sessionId = null;
        if (request()->hasSession()) {
            $sessionId = request()->session()->get('login_session_id');
        }

        if ($sessionId) {
            $this->sessionService->revokeSession($sessionId);
        } else {
            // Fallback: revoke latest active session for this user
            $active = LoginSession::where('user_id', $user->id)
                ->where('is_revoked', false)
                ->orderBy('last_active_at', 'desc')
                ->first();
            if ($active) {
                $this->sessionService->revokeSession($active->id);
            }
        }

        // 2. Record Activity Log
        ActivityService::record(
            ActivityType::Logout,
            "User {$user->name} logged out from IP {$ip}",
            $user
        );

        // 3. Dispatch domain event
        DomainUserLoggedOut::dispatch((string) $user->id);

        // 4. Clear context
        IdentityContext::clear();
    }

    /**
     * Handle password reset.
     */
    public function onPasswordReset(PasswordReset $event): void
    {
        $user = $event->user;

        ActivityService::record(
            ActivityType::PasswordChanged,
            "User {$user->name} reset their password",
            $user
        );
    }

    /**
     * Register listeners.
     */
    public function subscribe($events): array
    {
        return [
            Login::class => 'onLogin',
            Logout::class => 'onLogout',
            PasswordReset::class => 'onPasswordReset',
        ];
    }
}
