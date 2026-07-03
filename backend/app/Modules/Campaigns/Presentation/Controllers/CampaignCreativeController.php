<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Campaigns\Application\DTOs\UploadCreativeData;
use App\Modules\Campaigns\Application\Services\CampaignService;
use App\Modules\Campaigns\Presentation\Requests\UploadCreativeRequest;
use App\Modules\Campaigns\Presentation\Requests\AuditCreativeRequest;
use App\Modules\Campaigns\Presentation\Resources\CampaignCreativeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class CampaignCreativeController extends BaseApiController
{
    public function __construct(
        protected CampaignService $campaignService
    ) {}

    public function store(string $campaignId, UploadCreativeRequest $request): JsonResponse
    {
        $campaign = $this->campaignService->getDetails($campaignId);
        Gate::authorize('uploadCreative', $campaign);

        $dto = UploadCreativeData::fromRequest($request);
        $creative = $this->campaignService->uploadCreative($campaignId, $dto);

        return $this->successResponse(
            new CampaignCreativeResource($creative),
            'Creative file uploaded successfully.',
            201
        );
    }

    public function audit(string $campaignId, string $creativeId, AuditCreativeRequest $request): JsonResponse
    {
        Gate::authorize('auditCreative', Campaign::class);

        $creative = $this->campaignService->auditCreative(
            $campaignId,
            $creativeId,
            $request->input('status'),
            $request->input('rejection_reason')
        );

        return $this->successResponse(
            new CampaignCreativeResource($creative),
            'Creative audit completed successfully.'
        );
    }
}
