<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Inventory\Application\DTOs\InventoryFaceData;
use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Presentation\Resources\InventoryFaceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InventoryFaceController extends BaseApiController
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Add face to inventory.
     */
    public function store(string $inventoryId, Request $request): JsonResponse
    {
        $inventory = $this->inventoryService->getDetails($inventoryId);
        Gate::authorize('update', $inventory);

        $request->validate([
            'face_code' => ['required', 'string', 'max:50'],
            'display_name' => ['required', 'string', 'max:100'],
            'facing_direction' => ['required', 'string', 'in:north,south,east,west,northeast,northwest,southeast,southwest,omnidirectional'],
            'display_order' => ['sometimes', 'integer', 'min:1'],
            'physical_specifications' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $dto = InventoryFaceData::fromRequest($request);
        $face = $this->inventoryService->addFace($inventoryId, $dto);

        return $this->successResponse(
            new InventoryFaceResource($face),
            'Face added successfully.',
            201
        );
    }
}
