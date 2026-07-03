<?php

declare(strict_types=1);

namespace App\Modules\Finance\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Finance\Application\Services\FinanceService;
use App\Modules\Finance\Domain\Entities\ProviderSettlement;
use App\Modules\Finance\Presentation\Resources\ProviderSettlementResource;
use App\Modules\Finance\Domain\Enums\SettlementStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SettlementController extends BaseApiController
{
    public function __construct(protected FinanceService $financeService) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewSettlements', ProviderSettlement::class);

        $providerId = $request->query('provider_id');
        $settlements = $this->financeService->listSettlements($providerId, (int) $request->query('per_page', 15));

        return $this->successResponse(
            ProviderSettlementResource::collection($settlements)->response()->getData(true),
            'Provider settlements retrieved successfully.'
        );
    }

    public function payout(string $id, Request $request): JsonResponse
    {
        Gate::authorize('manageSettlements', ProviderSettlement::class);

        $request->validate([
            'payout_reference' => ['required', 'string', 'max:100'],
        ]);

        $settlement = ProviderSettlement::findOrFail($id);
        $settlement->update([
            'status' => SettlementStatus::Paid->value,
            'processed_at' => now(),
            'payout_reference' => $request->input('payout_reference'),
        ]);

        return $this->successResponse(
            new ProviderSettlementResource($settlement),
            'Settlement marked as paid successfully.'
        );
    }
}
