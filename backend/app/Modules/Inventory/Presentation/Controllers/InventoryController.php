<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Inventory\Application\DTOs\CreateInventoryData;
use App\Modules\Inventory\Application\DTOs\UpdateInventoryData;
use App\Modules\Inventory\Application\DTOs\InventoryFilterData;
use App\Modules\Inventory\Application\DTOs\UploadDocumentData;
use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Presentation\Requests\CreateInventoryRequest;
use App\Modules\Inventory\Presentation\Requests\UpdateInventoryRequest;
use App\Modules\Inventory\Presentation\Resources\InventoryResource;
use App\Modules\Inventory\Presentation\Resources\InventoryDetailResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InventoryController extends BaseApiController
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * List paginated inventories.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Inventory::class);

        $filters = InventoryFilterData::fromRequest($request);
        $inventories = $this->inventoryService->list($filters, (int) $request->query('per_page', 15));

        return $this->successResponse(
            InventoryResource::collection($inventories)->response()->getData(true),
            'Inventory list retrieved successfully.'
        );
    }

    /**
     * Create new inventory aggregate.
     */
    public function store(CreateInventoryRequest $request): JsonResponse
    {
        Gate::authorize('create', Inventory::class);

        $dto = CreateInventoryData::fromRequest($request);
        $inventory = $this->inventoryService->create($dto);

        return $this->successResponse(
            new InventoryResource($inventory),
            'Inventory created successfully.',
            201
        );
    }

    /**
     * Show aggregate details with loaded graph.
     */
    public function show(string $id): JsonResponse
    {
        $inventory = $this->inventoryService->getDetails($id);
        Gate::authorize('view', $inventory);

        $inventory->load(['faces.pricing', 'faces.availabilities', 'documents', 'inventoryMedia', 'activities']);

        return $this->successResponse(
            new InventoryDetailResource($inventory),
            'Inventory details retrieved successfully.'
        );
    }

    /**
     * Update inventory configurations.
     */
    public function update(string $id, UpdateInventoryRequest $request): JsonResponse
    {
        $inventory = $this->inventoryService->getDetails($id);
        Gate::authorize('update', $inventory);

        $dto = UpdateInventoryData::fromRequest($request);
        $updated = $this->inventoryService->update($id, $dto);

        return $this->successResponse(
            new InventoryResource($updated),
            'Inventory updated successfully.'
        );
    }

    /**
     * Transition operational status.
     */
    public function updateStatus(string $id, Request $request): JsonResponse
    {
        Gate::authorize('approve', Inventory::class);

        $request->validate(['status' => ['required', 'string']]);
        $updated = $this->inventoryService->changeStatus($id, $request->input('status'));

        return $this->successResponse(
            new InventoryResource($updated),
            'Inventory status updated successfully.'
        );
    }

    /**
     * Search inventories.
     */
    public function search(Request $request): JsonResponse
    {
        $filters = InventoryFilterData::fromRequest($request);
        $results = $this->inventoryService->search($filters, (int) $request->query('per_page', 15));

        return $this->successResponse(
            InventoryResource::collection($results)->response()->getData(true),
            'Search completed successfully.'
        );
    }

    /**
     * Nearby inventory query.
     */
    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'radius_km' => ['sometimes', 'numeric', 'min:0.1', 'max:500'],
        ]);

        $results = $this->inventoryService->getNearby(
            (float) $request->input('latitude'),
            (float) $request->input('longitude'),
            (float) $request->input('radius_km', 10),
            (int) $request->input('limit', 50)
        );

        return $this->successResponse(
            InventoryResource::collection($results),
            'Nearby inventory retrieved successfully.'
        );
    }

    /**
     * Dashboard metrics.
     */
    public function dashboard(): JsonResponse
    {
        $dashboard = $this->inventoryService->getDashboard();

        return $this->successResponse(
            $dashboard->toArray(),
            'Inventory dashboard metrics compiled successfully.'
        );
    }

    /**
     * Upload compliance document.
     */
    public function uploadDocument(string $id, Request $request): JsonResponse
    {
        $inventory = $this->inventoryService->getDetails($id);
        Gate::authorize('uploadDocuments', $inventory);

        $request->validate([
            'document_type' => ['required', 'string'],
            'file_path' => ['required', 'string'],
        ]);

        $dto = UploadDocumentData::fromRequest($request);
        $doc = $this->inventoryService->uploadDocument($id, $dto);

        return $this->successResponse(
            $doc->toArray(),
            'Document uploaded successfully.',
            201
        );
    }
}
