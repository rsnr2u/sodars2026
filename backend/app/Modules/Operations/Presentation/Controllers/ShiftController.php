<?php

declare(strict_types=1);

namespace App\Modules\Operations\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Operations\Domain\Entities\Shift;
use App\Modules\Operations\Domain\Entities\BusinessCalendar;
use App\Modules\Operations\Domain\Managers\ShiftLifecycleManager;
use App\Modules\Operations\Domain\Managers\CalendarLifecycleManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct(
        protected ShiftLifecycleManager $shiftManager,
        protected CalendarLifecycleManager $calendarManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orgId = $request->header('X-Organization-Id') ?? $request->get('organization_id');
        if (!$orgId) {
            return response()->json(['error' => 'Organization ID is required.'], 400);
        }

        $shifts = Shift::where('organization_id', $orgId)->get();

        return response()->json($shifts);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'organization_id' => 'required|uuid',
            'name' => 'required|string|max:150',
            'shift_pattern' => 'required|array',
        ]);

        $shift = $this->shiftManager->create($request->all());

        return response()->json($shift, 210);
    }

    public function storeCalendar(Request $request): JsonResponse
    {
        $request->validate([
            'organization_id' => 'required|uuid',
            'name' => 'required|string|max:150',
            'type' => 'required|string',
            'working_hours' => 'nullable|array',
            'holidays' => 'nullable|array',
        ]);

        $calendar = $this->calendarManager->create($request->all());

        return response()->json($calendar, 210);
    }
}
