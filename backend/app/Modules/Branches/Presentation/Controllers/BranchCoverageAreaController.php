<?php

declare(strict_types=1);

namespace App\Modules\Branches\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Branches\Application\DTOs\CoverageAreaData;
use App\Modules\Branches\Application\Services\BranchService;
use App\Modules\Branches\Presentation\Requests\AddCoverageAreaRequest;
use App\Modules\Branches\Presentation\Resources\BranchCoverageAreaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class BranchCoverageAreaController extends BaseApiController
{
    public function __construct(
        protected BranchService $branchService
    ) {}

    /**
     * List coverage city boundaries for a branch.
     */
    public function index(string $branchId): JsonResponse
    {
        $branch = $this->branchService->getDetails($branchId);

        Gate::authorize('view', $branch);

        $coverage = $this->branchService->coverage($branchId);

        return $this->successResponse(
            BranchCoverageAreaResource::collection($coverage),
            'Branch coverage areas list retrieved successfully.'
        );
    }

    /**
     * Add city coverage to branch bounds.
     */
    public function store(AddCoverageAreaRequest $request, string $branchId): JsonResponse
    {
        $branch = $this->branchService->getDetails($branchId);

        Gate::authorize('manageCoverage', $branch);

        $data = CoverageAreaData::fromRequest($request);
        $area = $this->branchService->addCoverageArea($branchId, $data);

        $area->load(['country', 'state', 'district', 'city']);

        return $this->successResponse(
            new BranchCoverageAreaResource($area),
            'Branch coverage area added successfully.',
            201
        );
    }

    /**
     * Remove city coverage from branch bounds.
     */
    public function destroy(string $branchId, string $areaId): JsonResponse
    {
        $branch = $this->branchService->getDetails($branchId);

        Gate::authorize('manageCoverage', $branch);

        $this->branchService->removeCoverageArea($branchId, $areaId);

        return $this->successResponse(
            null,
            'Branch coverage area removed successfully.'
        );
    }
}
