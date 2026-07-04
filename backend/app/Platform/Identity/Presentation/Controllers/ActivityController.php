<?php

declare(strict_types=1);

namespace App\Platform\Identity\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Platform\Identity\Application\Services\ActivityService;
use App\Platform\Identity\Application\Services\IdentityContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends BaseApiController
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orgId = IdentityContext::organizationId();
        if (!$orgId && !$request->user()?->hasRole('super_admin')) {
            return $this->successResponse([], 'Activity logs retrieved.');
        }

        $perPage = $this->getPerPage();

        if ($request->user()?->hasRole('super_admin') && !$orgId) {
            // Super Admin listing all logs if not scoped
            $logs = \App\Platform\Identity\Domain\Entities\ActivityLog::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } else {
            $logs = $this->activityService->getLogsForOrganization($orgId, $perPage);
        }

        return $this->successResponse($logs->toArray(), 'Activity logs retrieved.');
    }

    public function getEntityLogs(string $type, string $id): JsonResponse
    {
        // Enforce entity lookup format
        $subjectType = str_replace('-', '\\', $type);
        if (!class_exists($subjectType)) {
            // Fallback try common entities
            $aliases = [
                'user' => \App\Models\User::class,
                'booking' => \App\Modules\Bookings\Domain\Entities\Booking::class,
                'inventory' => \App\Modules\Inventory\Domain\Entities\Inventory::class,
            ];
            if (isset($aliases[strtolower($type)])) {
                $subjectType = $aliases[strtolower($type)];
            } else {
                return $this->errorResponse('Invalid subject type.', null, 400);
            }
        }

        $perPage = $this->getPerPage();
        $logs = $this->activityService->getLogsForEntity($subjectType, $id, $perPage);

        // Verify all returned logs belong to current organization (tenant boundary)
        $orgId = IdentityContext::organizationId();
        if ($orgId) {
            foreach ($logs as $log) {
                if ($log->organization_id !== $orgId) {
                    return $this->errorResponse('Access denied.', null, 403);
                }
            }
        }

        return $this->successResponse($logs->toArray(), 'Entity activity logs retrieved.');
    }
}
