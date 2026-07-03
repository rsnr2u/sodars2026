<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Campaigns\Application\DTOs\CreateCampaignData;
use App\Modules\Campaigns\Application\DTOs\UpdateCampaignData;
use App\Modules\Campaigns\Application\DTOs\CampaignFilterData;
use App\Modules\Campaigns\Application\Services\CampaignService;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Presentation\Requests\CreateCampaignRequest;
use App\Modules\Campaigns\Presentation\Requests\UpdateCampaignRequest;
use App\Modules\Campaigns\Presentation\Resources\CampaignResource;
use App\Modules\Campaigns\Presentation\Resources\CampaignDetailResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CampaignController extends BaseApiController
{
    public function __construct(
        protected CampaignService $campaignService
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Campaign::class);

        $filters = CampaignFilterData::fromRequest($request);
        $campaigns = $this->campaignService->list($filters, (int) $request->query('per_page', 15));

        return $this->successResponse(
            CampaignResource::collection($campaigns)->response()->getData(true),
            'Campaign list retrieved successfully.'
        );
    }

    public function store(CreateCampaignRequest $request): JsonResponse
    {
        Gate::authorize('create', Campaign::class);

        $dto = CreateCampaignData::fromRequest($request);
        $campaign = $this->campaignService->create($dto);

        return $this->successResponse(
            new CampaignResource($campaign),
            'Campaign created successfully.',
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $campaign = $this->campaignService->getDetails($id);
        Gate::authorize('view', $campaign);

        $campaign->load(['customer', 'branch', 'inventoryFaces', 'creatives', 'proofs', 'notes', 'activities']);

        return $this->successResponse(
            new CampaignDetailResource($campaign),
            'Campaign details retrieved successfully.'
        );
    }

    public function update(string $id, UpdateCampaignRequest $request): JsonResponse
    {
        $campaign = $this->campaignService->getDetails($id);
        Gate::authorize('update', $campaign);

        $dto = UpdateCampaignData::fromRequest($request);
        $updated = $this->campaignService->update($id, $dto);

        return $this->successResponse(
            new CampaignResource($updated),
            'Campaign updated successfully.'
        );
    }

    public function updateStatus(string $id, Request $request): JsonResponse
    {
        $campaign = $this->campaignService->getDetails($id);
        // Authorization relies on campaign update policy gates or branch override
        Gate::authorize('update', $campaign);

        $request->validate(['status' => ['required', 'string']]);
        $updated = $this->campaignService->changeStatus($id, $request->input('status'));

        return $this->successResponse(
            new CampaignResource($updated),
            'Campaign status updated successfully.'
        );
    }

    public function dashboard(Request $request): JsonResponse
    {
        // View dashboard counts
        $customerId = $request->query('customer_id');
        $dashboard = $this->campaignService->getDashboard($customerId);

        return $this->successResponse(
            $dashboard->toArray(),
            'Campaign dashboard metrics compiled successfully.'
        );
    }
}
