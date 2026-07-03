<?php

declare(strict_types=1);

namespace App\Modules\Branches\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Branches\Application\DTOs\BranchMemberData;
use App\Modules\Branches\Application\Services\BranchService;
use App\Modules\Branches\Presentation\Requests\AssignBranchMemberRequest;
use App\Modules\Branches\Presentation\Resources\BranchMemberResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class BranchMemberController extends BaseApiController
{
    public function __construct(
        protected BranchService $branchService
    ) {}

    /**
     * List member users registered under a branch.
     */
    public function index(string $branchId): JsonResponse
    {
        $branch = $this->branchService->getDetails($branchId);

        Gate::authorize('view', $branch);

        $members = $branch->members;
        $members->load(['user']);

        return $this->successResponse(
            BranchMemberResource::collection($members),
            'Branch members list retrieved successfully.'
        );
    }

    /**
     * Assign a user as a member (e.g. manager/staff) of the branch.
     */
    public function store(AssignBranchMemberRequest $request, string $branchId): JsonResponse
    {
        $branch = $this->branchService->getDetails($branchId);

        Gate::authorize('manageMembers', $branch);

        $data = BranchMemberData::fromRequest($request);
        $member = $this->branchService->assignMember($branchId, $data);

        $member->load(['user']);

        return $this->successResponse(
            new BranchMemberResource($member),
            'Branch member assigned successfully.',
            201
        );
    }

    /**
     * Mark membership as inactive.
     */
    public function destroy(string $branchId, string $memberId): JsonResponse
    {
        $branch = $this->branchService->getDetails($branchId);

        Gate::authorize('manageMembers', $branch);

        $this->branchService->removeMember($branchId, $memberId);

        return $this->successResponse(
            null,
            'Branch member removed successfully.'
        );
    }
}
