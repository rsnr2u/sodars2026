<?php

declare(strict_types=1);

namespace App\Platform\Identity\Infrastructure\Middleware;

use App\Platform\Identity\Application\Services\IdentityContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceOrganizationScope
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Initialize all platform contexts (Trace, Identity, etc.)
        \App\Core\Context\ContextManager::boot();

        // Enforce organization context for tenant-scoped actions
        // If a user belongs to no organization and isn't a super_admin, we can block or let it proceed depending on policy.
        // For security, if they are logged in, we expect them to either have organization_id set or have organization memberships.
        $user = IdentityContext::user();
        if ($user && !$user->hasRole('super_admin') && !IdentityContext::organizationId()) {
            return response()->json([
                'success' => false,
                'message' => 'User does not belong to an active organization.',
                'errors' => ['organization' => ['Access denied: Tenant organization context missing.']],
            ], 403);
        }

        return $next($request);
    }
}
