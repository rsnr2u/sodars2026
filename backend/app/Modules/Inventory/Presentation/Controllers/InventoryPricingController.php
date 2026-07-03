<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Inventory\Application\DTOs\InventoryPricingData;
use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Presentation\Requests\UpdatePricingRequest;
use App\Modules\Inventory\Presentation\Resources\InventoryPricingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class InventoryPricingController extends BaseApiController
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Add pricing rate to face.
     */
    public function store(string $inventoryId, string $faceId, UpdatePricingRequest $request): JsonResponse
    {
        $inventory = $this->inventoryService->getDetails($inventoryId);
        Gate::authorize('managePricing', $inventory);

        $dto = InventoryPricingData::fromRequest($request);
        $pricing = $this->inventoryService->updatePricing($faceId, $dto);

        return $this->successResponse(
            new InventoryPricingResource($pricing),
            'Pricing rate created successfully.',
            201
        );
    }
}
