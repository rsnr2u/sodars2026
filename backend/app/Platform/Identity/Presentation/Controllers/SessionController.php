<?php

declare(strict_types=1);

namespace App\Platform\Identity\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Platform\Identity\Application\Services\SessionService;
use App\Platform\Identity\Application\Services\IdentityContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends BaseApiController
{
    public function __construct(
        protected SessionService $sessionService
    ) {}

    public function index(): JsonResponse
    {
        $userId = IdentityContext::userId();
        if (!$userId) {
            return $this->errorResponse('Unauthenticated.', null, 401);
        }

        $sessions = $this->sessionService->getActiveSessions($userId);
        return $this->successResponse($sessions->toArray(), 'Active sessions retrieved.');
    }

    public function destroy(string $id): JsonResponse
    {
        $userId = IdentityContext::userId();
        if (!$userId) {
            return $this->errorResponse('Unauthenticated.', null, 401);
        }

        $revoked = $this->sessionService->revokeSession($id);
        if ($revoked) {
            return $this->successResponse(null, 'Session revoked successfully.');
        }

        return $this->errorResponse('Session not found.', null, 404);
    }

    public function revokeOthers(Request $request): JsonResponse
    {
        $userId = IdentityContext::userId();
        if (!$userId) {
            return $this->errorResponse('Unauthenticated.', null, 401);
        }

        $currentSessionId = '';
        if ($request->hasSession()) {
            $currentSessionId = (string) $request->session()->get('login_session_id');
        }

        $count = $this->sessionService->revokeOtherSessions($userId, $currentSessionId);
        return $this->successResponse(['revoked_count' => $count], 'Other sessions revoked successfully.');
    }
}
