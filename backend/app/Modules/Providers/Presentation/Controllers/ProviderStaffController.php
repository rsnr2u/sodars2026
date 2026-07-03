<?php

declare(strict_types=1);

namespace App\Modules\Providers\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Providers\Application\DTOs\AddStaffData;
use App\Modules\Providers\Application\Services\ProviderService;
use App\Modules\Providers\Presentation\Requests\AddStaffRequest;
use App\Modules\Providers\Presentation\Resources\ProviderMemberResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ProviderStaffController extends BaseApiController
{
    public function __construct(
        protected ProviderService $providerService
    ) {}

    /**
     * Invite staff member.
     */
    public function add(string $providerId, AddStaffRequest $request): JsonResponse
    {
        $provider = $this->providerService->getProviderDetails($providerId);
        Gate::authorize('manageStaff', $provider);

        $dto = AddStaffData::fromRequest($request);
        $staff = $this->providerService->addStaff($providerId, $dto);

        return $this->successResponse(
            new ProviderMemberResource($staff),
            'Staff member added to workspace successfully.',
            201
        );
    }

    /**
     * Revoke staff member workspace access.
     */
    public function remove(string $providerId, string $staffId): JsonResponse
    {
        $provider = $this->providerService->getProviderDetails($providerId);
        Gate::authorize('manageStaff', $provider);

        $this->providerService->removeStaff($providerId, $staffId);

        return $this->successResponse(
            null,
            'Staff workspace access revoked successfully.'
        );
    }
}
