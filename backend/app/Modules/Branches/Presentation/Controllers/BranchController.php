<?php

declare(strict_types=1);

namespace App\Modules\Branches\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Branches\Application\DTOs\BranchFilterData;
use App\Modules\Branches\Application\DTOs\CreateBranchData;
use App\Modules\Branches\Application\DTOs\UpdateBranchData;
use App\Modules\Branches\Application\Services\BranchService;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Branches\Presentation\Requests\ChangeBranchStatusRequest;
use App\Modules\Branches\Presentation\Requests\CreateBranchRequest;
use App\Modules\Branches\Presentation\Requests\UpdateBranchRequest;
use App\Modules\Branches\Presentation\Resources\BranchCollection;
use App\Modules\Branches\Presentation\Resources\BranchDetailResource;
use App\Modules\Branches\Presentation\Resources\BranchResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BranchController extends BaseApiController
{
    public function __construct(
        protected BranchService $branchService
    ) {}

    /**
     * Display a listing of branches.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Branch::class);

        $filters = BranchFilterData::fromRequest($request);
        $perPage = $this->getPerPage();
        $branches = $this->branchService->list($filters, $perPage);

        return $this->successResponse(
            new BranchCollection($branches),
            'Branches list retrieved successfully.'
        );
    }

    /**
     * Display a specific branch with relations.
     */
    public function show(string $id): JsonResponse
    {
        $branch = $this->branchService->getDetails($id);

        Gate::authorize('view', $branch);

        return $this->successResponse(
            new BranchDetailResource($branch),
            'Branch details retrieved successfully.'
        );
    }

    /**
     * Store a newly created branch.
     */
    public function store(CreateBranchRequest $request): JsonResponse
    {
        Gate::authorize('create', Branch::class);

        $data = CreateBranchData::fromRequest($request);
        $branch = $this->branchService->createBranch($data);

        return $this->successResponse(
            new BranchResource($branch),
            'Branch created successfully.',
            201
        );
    }

    /**
     * Update the branch profile.
     */
    public function update(UpdateBranchRequest $request, string $id): JsonResponse
    {
        $branch = $this->branchService->getDetails($id);

        Gate::authorize('update', $branch);

        $data = UpdateBranchData::fromRequest($request);
        $updated = $this->branchService->updateBranch($id, $data);

        return $this->successResponse(
            new BranchResource($updated),
            'Branch updated successfully.'
        );
    }

    /**
     * Change the branch operational status.
     */
    public function changeStatus(ChangeBranchStatusRequest $request, string $id): JsonResponse
    {
        $branch = $this->branchService->getDetails($id);

        Gate::authorize('changeStatus', $branch);

        $newStatus = $request->input('status');
        $updated = $this->branchService->changeStatus($id, $newStatus);

        return $this->successResponse(
            new BranchResource($updated),
            'Branch status updated successfully.'
        );
    }

    /**
     * Retrieve aggregated dashboard metrics for branch manager.
     */
    public function dashboard(string $id): JsonResponse
    {
        $branch = $this->branchService->getDetails($id);

        Gate::authorize('view', $branch);

        $metrics = $this->branchService->dashboard($id);

        return $this->successResponse(
            $metrics,
            'Branch dashboard metrics compiled successfully.'
        );
    }
}
