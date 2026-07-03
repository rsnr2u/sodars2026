<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Presentation\Requests\UpdateAvailabilityRequest;
use App\Modules\Inventory\Presentation\Resources\InventoryAvailabilityResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class InventoryAvailabilityController extends BaseApiController
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * List availability slots for a face.
     */
    public function index(string $inventoryId, string $faceId): JsonResponse
    {
        $inventory = $this->inventoryService->getDetails($inventoryId);
        Gate::authorize('view', $inventory);

        $availabilities = $this->inventoryService->getAvailability($faceId);

        return $this->successResponse(
            InventoryAvailabilityResource::collection($availabilities),
            'Availability slots retrieved successfully.'
        );
    }

    /**
     * Block availability slot.
     */
    public function store(string $inventoryId, string $faceId, UpdateAvailabilityRequest $request): JsonResponse
    {
        $inventory = $this->inventoryService->getDetails($inventoryId);
        Gate::authorize('manageAvailability', $inventory);

        $availability = $this->inventoryService->block(
            $faceId,
            Carbon::parse($request->input('start_at')),
            Carbon::parse($request->input('end_at')),
            $request->input('availability_status'),
            $request->input('reason'),
            $request->input('remarks')
        );

        return $this->successResponse(
            new InventoryAvailabilityResource($availability),
            'Availability block created successfully.',
            201
        );
    }

    /**
     * Unblock availability slot.
     */
    public function destroy(string $inventoryId, string $faceId, string $availabilityId): JsonResponse
    {
        $inventory = $this->inventoryService->getDetails($inventoryId);
        Gate::authorize('manageAvailability', $inventory);

        $this->inventoryService->unblock($faceId, $availabilityId);

        return $this->successResponse(
            null,
            'Availability block removed successfully.'
        );
    }
}
