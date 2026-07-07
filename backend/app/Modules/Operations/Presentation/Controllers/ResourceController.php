<?php

declare(strict_types=1);

namespace App\Modules\Presentation\Controllers; // Wait! Let's use exact same namespace App\Modules\Operations\Presentation\Controllers;

namespace App\Modules\Operations\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Operations\Domain\Entities\Schedule;
use App\Modules\Operations\Domain\Entities\OperationalResource;
use App\Modules\Operations\Domain\Entities\ResourceAvailabilityProjection;
use App\Modules\Operations\Domain\Entities\ResourceWorkloadProjection;
use App\Modules\Operations\Domain\Services\OperationsLifecycleService;
use App\Modules\Operations\Domain\Services\OptimizationEngine;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function __construct(
        protected OperationsLifecycleService $lifecycleService,
        protected OptimizationEngine $optimizationEngine
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orgId = $request->header('X-Organization-Id') ?? $request->get('organization_id');
        if (!$orgId) {
            return response()->json(['error' => 'Organization ID is required.'], 400);
        }

        $query = OperationalResource::where('organization_id', $orgId);

        if ($request->has('resource_type')) {
            $query->where('resource_type', $request->get('resource_type'));
        }

        $resources = $query->get();

        return response()->json($resources);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'organization_id' => 'required|uuid',
            'resource_type' => 'required|string',
            'display_name' => 'required|string|max:150',
            'skills' => 'required|array',
        ]);

        $resource = app(\App\Modules\Operations\Domain\Managers\ResourceLifecycleManager::class)->create($request->all());

        return response()->json($resource, 210);
    }

    public function assign(Request $request, Schedule $schedule): JsonResponse
    {
        $request->validate([
            'resource_id' => 'required|uuid',
        ]);

        $resource = OperationalResource::findOrFail($request->get('resource_id'));
        $this->lifecycleService->assignResource($schedule, $resource);

        return response()->json([
            'message' => 'Resource successfully assigned to schedule.',
            'schedule' => $schedule->fresh(['assignments.resource', 'conflicts'])
        ]);
    }

    public function release(Request $request, Schedule $schedule): JsonResponse
    {
        $request->validate([
            'resource_id' => 'required|uuid',
            'released_reason' => 'required|string',
        ]);

        $resource = OperationalResource::findOrFail($request->get('resource_id'));
        $this->lifecycleService->releaseResource($schedule, $resource, $request->get('released_reason'));

        return response()->json([
            'message' => 'Resource successfully released from schedule.'
        ]);
    }

    public function optimize(Request $request): JsonResponse
    {
        $request->validate([
            'organization_id' => 'required|uuid',
            'required_skills' => 'required|array',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $orgId = $request->get('organization_id');
        $skills = $request->get('required_skills');
        $start = Carbon::parse($request->get('start_time'));
        $end = Carbon::parse($request->get('end_time'));

        $resources = OperationalResource::where('organization_id', $orgId)->get()->all();

        $result = $this->optimizationEngine->optimize($resources, $skills, $start, $end);

        return response()->json($result->toArray());
    }
}
