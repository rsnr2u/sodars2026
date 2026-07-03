<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Platform\Reporting\Domain\Entities\Dashboard;
use App\Platform\Reporting\Domain\Entities\DashboardWidget;
use App\Platform\Reporting\Application\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends BaseApiController
{
    public function __construct(
        protected DashboardService $service
    ) {}

    /**
     * List user dashboards.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $dashboards = Dashboard::where('user_id', $userId)->get();

        return $this->successResponse($dashboards, 'User dashboards retrieved.');
    }

    /**
     * Store a dashboard config.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'is_default' => 'nullable|boolean',
            'layout_config' => 'nullable|array',
        ]);

        $userId = (string) $request->user()->id;

        $isDefault = (bool) $request->input('is_default', false);
        if ($isDefault) {
            Dashboard::where('user_id', $userId)->update(['is_default' => false]);
        }

        $dashboard = Dashboard::create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'name' => $request->input('name'),
            'is_default' => $isDefault,
            'layout_config' => $request->input('layout_config'),
        ]);

        return $this->successResponse($dashboard, 'Dashboard created successfully.', 201);
    }

    /**
     * Show dashboard details.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $dashboard = Dashboard::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        return $this->successResponse($dashboard, 'Dashboard configuration.');
    }

    /**
     * Fetch rendered visual widget payloads.
     */
    public function widgets(string $id, Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $dashboard = Dashboard::with('widgets')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $rendered = $this->service->renderWidgets($dashboard);

        return $this->successResponse(['widgets' => $rendered], 'Dashboard widgets values rendered.');
    }

    /**
     * Append widget configuration to dashboard workspace.
     */
    public function addWidget(string $id, Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $dashboard = Dashboard::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $request->validate([
            'report_key' => 'required|string',
            'widget_type' => 'required|string',
            'title' => 'required|string|max:150',
            'dimensions' => 'required|array',
            'dimensions.x' => 'required|integer',
            'dimensions.y' => 'required|integer',
            'dimensions.width' => 'required|integer',
            'dimensions.height' => 'required|integer',
            'query_parameters' => 'nullable|array',
            'drilldown_route' => 'nullable|string',
        ]);

        $widget = DashboardWidget::create([
            'id' => (string) Str::uuid(),
            'dashboard_id' => $dashboard->id,
            'report_key' => $request->input('report_key'),
            'widget_type' => $request->input('widget_type'),
            'title' => $request->input('title'),
            'dimensions' => $request->input('dimensions'),
            'query_parameters' => $request->input('query_parameters'),
            'drilldown_route' => $request->input('drilldown_route'),
        ]);

        return $this->successResponse($widget, 'Widget appended successfully.', 201);
    }
}
