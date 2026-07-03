<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Platform\Reporting\Infrastructure\Registry\ReportingRegistry;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Reporting\Domain\Entities\ReportExecution;
use App\Platform\Reporting\Application\Services\ReportExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends BaseApiController
{
    public function __construct(
        protected ReportingRegistry $registry,
        protected ReportExportService $exportService,
        protected \App\Platform\DAM\Application\Services\DAMService $damService
    ) {}

    /**
     * List all registered reports.
     */
    public function index(): JsonResponse
    {
        return $this->successResponse([
            'reports' => [
                'trial_balance' => 'Trial Balance Report',
                'inventory_occupancy' => 'Inventory Occupancy Report',
                'booking_performance' => 'Booking Performance Report',
            ]
        ], 'Registered reports list.');
    }

    /**
     * Get report schema details.
     */
    public function show(string $key): JsonResponse
    {
        try {
            $report = $this->registry->resolveReport($key);
            return $this->successResponse([
                'key' => $key,
                'parameters_schema' => $report::getParameterSchema()
            ], 'Report configuration.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }

    /**
     * Run a report and return query results payload.
     */
    public function run(string $key, Request $request): JsonResponse
    {
        try {
            $report = $this->registry->resolveReport($key);
            
            $schema = $report::getParameterSchema();
            $validated = $request->validate($schema);

            $params = ReportParameters::fromArray($validated);
            $result = $report->generate($params);

            return $this->successResponse($result, 'Report generated successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    /**
     * Export report to DAM.
     */
    public function export(string $key, Request $request): JsonResponse
    {
        try {
            $report = $this->registry->resolveReport($key);
            $schema = $report::getParameterSchema();
            $validated = $request->validate($schema);

            $userId = (string) $request->user()->id;

            $asset = $this->exportService->exportToDam($key, $validated, $userId);

            return $this->successResponse([
                'asset_id' => $asset->id,
                'title' => $asset->title,
                'download_url' => $this->damService->getUrl($asset->currentVersion->file->storage_path ?? ''),
            ], 'Report exported successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    /**
     * Fetch report execution log details.
     */
    public function getExecution(string $id): JsonResponse
    {
        $execution = ReportExecution::with(['scheduledReport', 'executedBy'])->find($id);
        if (!$execution) {
            return $this->errorResponse('Execution log not found.', null, 404);
        }
        return $this->successResponse($execution, 'Execution log details.');
    }
}
