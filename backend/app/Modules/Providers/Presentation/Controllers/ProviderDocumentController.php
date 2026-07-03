<?php

declare(strict_types=1);

namespace App\Modules\Providers\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Providers\Application\DTOs\UploadDocumentData;
use App\Modules\Providers\Application\Services\ProviderService;
use App\Modules\Providers\Presentation\Requests\AuditDocumentRequest;
use App\Modules\Providers\Presentation\Requests\UploadDocumentRequest;
use App\Modules\Providers\Presentation\Resources\ProviderDocumentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ProviderDocumentController extends BaseApiController
{
    public function __construct(
        protected ProviderService $providerService
    ) {}

    /**
     * Upload verification document.
     */
    public function upload(string $providerId, UploadDocumentRequest $request): JsonResponse
    {
        $provider = $this->providerService->getProviderDetails($providerId);
        Gate::authorize('update', $provider);

        $dto = UploadDocumentData::fromRequest($request);
        $doc = $this->providerService->uploadDocument($providerId, $dto);

        return $this->successResponse(
            new ProviderDocumentResource($doc),
            'Compliance document uploaded successfully.',
            201
        );
    }

    /**
     * Audit compliance documents.
     */
    public function audit(string $providerId, string $docId, AuditDocumentRequest $request): JsonResponse
    {
        $provider = $this->providerService->getProviderDetails($providerId);
        Gate::authorize('audit', $provider);

        $doc = $this->providerService->auditDocument(
            $providerId,
            $docId,
            $request->input('status'),
            $request->input('remarks')
        );

        return $this->successResponse(
            new ProviderDocumentResource($doc),
            'Compliance document audited successfully.'
        );
    }
}
