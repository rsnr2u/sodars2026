<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Campaigns\Application\DTOs\UploadProofData;
use App\Modules\Campaigns\Application\Services\CampaignService;
use App\Modules\Campaigns\Presentation\Requests\UploadProofRequest;
use App\Modules\Campaigns\Presentation\Requests\AuditProofRequest;
use App\Modules\Campaigns\Presentation\Resources\CampaignProofResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class CampaignProofController extends BaseApiController
{
    public function __construct(
        protected CampaignService $campaignService
    ) {}

    public function store(string $campaignId, UploadProofRequest $request): JsonResponse
    {
        $campaign = $this->campaignService->getDetails($campaignId);
        Gate::authorize('uploadProof', $campaign);

        $dto = UploadProofData::fromRequest($request);
        $proof = $this->campaignService->uploadProof($campaignId, $dto);

        return $this->successResponse(
            new CampaignProofResource($proof),
            'Proof-of-performance uploaded successfully.',
            201
        );
    }

    public function audit(string $campaignId, string $proofId, AuditProofRequest $request): JsonResponse
    {
        Gate::authorize('auditProof', Campaign::class);

        $proof = $this->campaignService->auditProof(
            $campaignId,
            $proofId,
            $request->input('status')
        );

        return $this->successResponse(
            new CampaignProofResource($proof),
            'Proof audit completed successfully.'
        );
    }
}
