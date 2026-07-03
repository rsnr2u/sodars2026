<?php

declare(strict_types=1);

namespace App\Platform\Shared\Presentation\Controllers;

use App\Core\Services\HealthService;
use App\Http\Controllers\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends BaseApiController
{
    public function __construct(
        protected HealthService $healthService
    ) {}

    /**
     * GET /health/live
     *
     * Fast liveness probe. Always public.
     * Returns 200 if the process is alive.
     */
    public function live(): JsonResponse
    {
        return response()->json($this->healthService->live(), 200);
    }

    /**
     * GET /health/ready
     *
     * Readiness probe. Public for load balancers / Kubernetes.
     * Checks database, cache, and storage connectivity.
     */
    public function ready(): JsonResponse
    {
        $report = $this->healthService->ready();
        $httpCode = $report['status'] === 'UP' ? 200 : 503;

        return response()->json($report, $httpCode);
    }

    /**
     * GET /health/details
     *
     * Detailed diagnostics. Restricted to:
     *   - Super Admin (authenticated user with 'super-admin' role)
     *   - APP_DEBUG=true (development environments)
     *
     * Returns latency metrics, versions, git hash, and full check details.
     */
    public function details(Request $request): JsonResponse
    {
        if (!$this->isAuthorizedForDetails($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Health details require super admin access or APP_DEBUG=true.',
            ], 403);
        }

        $report = $this->healthService->details();
        $httpCode = $report['status'] === 'UP' ? 200 : 503;

        return response()->json($report, $httpCode);
    }

    /**
     * Determine if the current request is authorized to view health details.
     *
     * Allowed when:
     *   - APP_DEBUG is true (development)
     *   - The authenticated user has a 'super-admin' role
     *   - The config allows unauthenticated access (health.details_requires_auth = false)
     */
    protected function isAuthorizedForDetails(Request $request): bool
    {
        // Always allow in debug mode
        if (config('app.debug') === true) {
            return true;
        }

        // If auth is not required by config, allow access
        if (config('foundation.health.details_requires_auth') === false) {
            return true;
        }

        // Check if the authenticated user has super-admin role
        $user = $request->user();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return true;
        }

        return false;
    }
}
