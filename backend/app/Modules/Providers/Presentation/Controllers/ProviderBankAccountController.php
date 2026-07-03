<?php

declare(strict_types=1);

namespace App\Modules\Providers\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Providers\Application\DTOs\UpdateBankAccountData;
use App\Modules\Providers\Application\Services\ProviderService;
use App\Modules\Providers\Presentation\Requests\UpdateBankAccountRequest;
use App\Modules\Providers\Presentation\Resources\ProviderBankAccountResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ProviderBankAccountController extends BaseApiController
{
    public function __construct(
        protected ProviderService $providerService
    ) {}

    /**
     * Configure payout parameters.
     */
    public function update(string $providerId, UpdateBankAccountRequest $request): JsonResponse
    {
        $provider = $this->providerService->getProviderDetails($providerId);
        Gate::authorize('update', $provider);

        $dto = UpdateBankAccountData::fromRequest($request);
        $account = $this->providerService->updateBankAccount($providerId, $dto);

        return $this->successResponse(
            new ProviderBankAccountResource($account),
            'Payout bank account details updated successfully.'
        );
    }
}
