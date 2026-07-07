<?php

declare(strict_types=1);

namespace App\Modules\Operations\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Operations\Domain\Entities\Schedule;
use App\Modules\Operations\Domain\Enums\ScheduleStatus;
use App\Modules\Operations\Domain\Services\OperationsLifecycleService;
use App\Modules\Operations\Domain\Services\OperationsMetricsEngine;
use App\Modules\Operations\Domain\ValueObjects\RecurrencePattern;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(
        protected OperationsLifecycleService $lifecycleService,
        protected OperationsMetricsEngine $metricsEngine
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orgId = $request->header('X-Organization-Id') ?? $request->get('organization_id');
        if (!$orgId) {
            return response()->json(['error' => 'Organization ID is required.'], 400);
        }

        $query = Schedule::where('organization_id', $orgId);

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('schedule_type')) {
            $query->where('schedule_type', $request->get('schedule_type'));
        }

        $schedules = $query->with(['execution', 'assignments', 'shift', 'calendar'])->get();

        return response()->json($schedules);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'organization_id' => 'required|uuid',
            'name' => 'required|string|max:150',
            'schedule_type' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $schedule = $this->lifecycleService->createSchedule($request->all());

        return response()->json($schedule->load('execution'), 210);
    }

    public function show(Schedule $schedule): JsonResponse
    {
        return response()->json($schedule->load([
            'execution',
            'assignments.resource',
            'conflicts',
            'checkpoints'
        ]));
    }

    public function transition(Request $request, Schedule $schedule): JsonResponse
    {
        $request->validate([
            'status' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        $status = ScheduleStatus::from($request->get('status'));
        $this->lifecycleService->transitionSchedule($schedule, $status, $request->get('reason'));

        return response()->json([
            'message' => "Schedule successfully transitioned to {$status->value}.",
            'schedule' => $schedule->fresh(['execution'])
        ]);
    }

    public function telemetry(Request $request, Schedule $schedule): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'speed_kph' => 'required|numeric',
        ]);

        $this->lifecycleService->recordTelemetryUpdate(
            $schedule,
            (float) $request->get('latitude'),
            (float) $request->get('longitude'),
            (float) $request->get('speed_kph')
        );

        return response()->json([
            'message' => 'Telemetry coordinates log successfully recorded.',
            'execution' => $schedule->execution()->first()
        ]);
    }

    public function recurrences(Request $request, Schedule $schedule): JsonResponse
    {
        $request->validate([
            'frequency' => 'required|string|in:daily,weekly,monthly',
            'interval' => 'integer|min:1',
            'by_days' => 'nullable|array',
            'exception_dates' => 'nullable|array',
            'ends_at' => 'nullable|date',
        ]);

        $pattern = new RecurrencePattern(
            $request->get('frequency'),
            (int) $request->get('interval', 1),
            $request->get('by_days', []),
            $request->get('exception_dates', []),
            $request->get('ends_at')
        );

        $createdOccurrences = $this->lifecycleService->generateScheduleRecurrences($schedule, $pattern);

        return response()->json([
            'message' => 'Recurrence rule successfully configured and future occurrences generated.',
            'occurrences_count' => count($createdOccurrences),
            'occurrences' => collect($createdOccurrences)->map(fn($s) => $s->toArray())
        ]);
    }

    public function metrics(Request $request): JsonResponse
    {
        $orgId = $request->header('X-Organization-Id') ?? $request->get('organization_id');
        if (!$orgId) {
            return response()->json(['error' => 'Organization ID is required.'], 400);
        }

        $metrics = $this->metricsEngine->getSummary($orgId);

        return response()->json($metrics);
    }
}
