<?php

declare(strict_types=1);

namespace App\Modules\Providers\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Providers\Application\DTOs\ProviderFilterData;
use App\Modules\Providers\Application\DTOs\RegisterProviderData;
use App\Modules\Providers\Application\Services\ProviderService;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Presentation\Requests\ChangeProviderStatusRequest;
use App\Modules\Providers\Presentation\Requests\RegisterProviderRequest;
use App\Modules\Providers\Presentation\Resources\ProviderDetailResource;
use App\Modules\Providers\Presentation\Resources\ProviderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProviderController extends BaseApiController
{
    public function __construct(
        protected ProviderService $providerService
    ) {}

    /**
     * List all providers.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Provider::class);

        $filters = ProviderFilterData::fromRequest($request);
        $providers = $this->providerService->listProviders($filters, (int) $request->query('per_page', 15));

        return $this->successResponse(
            ProviderResource::collection($providers)->response()->getData(true),
            'Providers list retrieved successfully.'
        );
    }

    /**
     * Guest/Public Registration.
     */
    public function store(RegisterProviderRequest $request): JsonResponse
    {
        $dto = RegisterProviderData::fromRequest($request);
        $provider = $this->providerService->registerProvider($dto);

        return $this->successResponse(
            new ProviderResource($provider),
            'Provider registered successfully in draft status.',
            201
        );
    }

    /**
     * Show details.
     */
    public function show(string $id): JsonResponse
    {
        $provider = $this->providerService->getProviderDetails($id);
        Gate::authorize('view', $provider);

        return $this->successResponse(
            new ProviderDetailResource($provider),
            'Provider details retrieved successfully.'
        );
    }

    /**
     * Perform manager audits on operational status.
     */
    public function updateStatus(string $id, ChangeProviderStatusRequest $request): JsonResponse
    {
        $provider = $this->providerService->getProviderDetails($id);
        Gate::authorize('audit', $provider);

        $updated = $this->providerService->changeProviderStatus($id, $request->input('status'));

        return $this->successResponse(
            new ProviderResource($updated),
            'Provider status updated successfully.'
        );
    }

    /**
     * Compile workspace dashboard metrics.
     */
    public function dashboard(string $id): JsonResponse
    {
        $provider = $this->providerService->getProviderDetails($id);
        Gate::authorize('view', $provider);

        $dashboard = $this->providerService->getProviderDashboard($id);

        return $this->successResponse(
            $dashboard->toArray(),
            'Provider dashboard metrics compiled successfully.'
        );
    }
}
