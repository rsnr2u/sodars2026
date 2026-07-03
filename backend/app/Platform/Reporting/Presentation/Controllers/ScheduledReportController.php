<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Platform\Reporting\Domain\Entities\ScheduledReport;
use App\Platform\Reporting\Application\Services\ScheduledReportService;
use App\Platform\Reporting\Application\Jobs\RunScheduledReportJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduledReportController extends BaseApiController
{
    public function __construct(
        protected ScheduledReportService $service
    ) {}

    /**
     * List user schedules.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $scheduled = ScheduledReport::where('user_id', $userId)->get();

        return $this->successResponse($scheduled, 'Scheduled reports retrieved.');
    }

    /**
     * Create a scheduled report entry.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'report_key' => 'required|string',
            'name' => 'required|string|max:150',
            'cron_expression' => 'required|string',
            'query_parameters' => 'nullable|array',
            'recipient_emails' => 'required|array',
            'recipient_emails.*' => 'required|email',
            'export_format' => 'nullable|string|in:csv',
        ]);

        $userId = (string) $request->user()->id;

        $scheduled = $this->service->create(
            $userId,
            $request->input('report_key'),
            $request->input('name'),
            $request->input('cron_expression'),
            $request->input('query_parameters', []),
            $request->input('recipient_emails'),
            $request->input('export_format', 'csv')
        );

        return $this->successResponse($scheduled, 'Report scheduled successfully.', 201);
    }

    /**
     * Dispatch RunScheduledReportJob immediately.
     */
    public function runNow(string $id, Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $scheduled = ScheduledReport::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        RunScheduledReportJob::dispatch($scheduled->id);

        return $this->successResponse(null, 'Scheduled report job queued successfully.');
    }
}
