<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Services;

use App\Modules\Inventory\Application\Actions\CreateInventoryAction;
use App\Modules\Inventory\Application\Actions\UpdateInventoryAction;
use App\Modules\Inventory\Application\Actions\ChangeInventoryStatusAction;
use App\Modules\Inventory\Application\Actions\AddInventoryFaceAction;
use App\Modules\Inventory\Application\Actions\UpdatePricingAction;
use App\Modules\Inventory\Application\Actions\BlockInventoryAction;
use App\Modules\Inventory\Application\Actions\UnblockInventoryAction;
use App\Modules\Inventory\Application\Actions\UploadInventoryDocumentAction;
use App\Modules\Inventory\Application\Queries\ListInventoryQuery;
use App\Modules\Inventory\Application\Queries\SearchInventoryQuery;
use App\Modules\Inventory\Application\Queries\NearbyInventoryQuery;
use App\Modules\Inventory\Application\Queries\InventoryDashboardQuery;
use App\Modules\Inventory\Application\Queries\InventoryAvailabilityQuery;
use App\Modules\Inventory\Application\DTOs\CreateInventoryData;
use App\Modules\Inventory\Application\DTOs\UpdateInventoryData;
use App\Modules\Inventory\Application\DTOs\InventoryFaceData;
use App\Modules\Inventory\Application\DTOs\InventoryPricingData;
use App\Modules\Inventory\Application\DTOs\InventoryFilterData;
use App\Modules\Inventory\Application\DTOs\UploadDocumentData;
use App\Modules\Inventory\Application\DTOs\InventoryDashboardDTO;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Inventory\Domain\Entities\InventoryPricing;
use App\Modules\Inventory\Domain\Entities\InventoryAvailability;
use App\Modules\Inventory\Domain\Entities\InventoryDocument;
use App\Modules\Inventory\Domain\Repositories\InventoryReadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class InventoryService
{
    public function __construct(
        protected InventoryReadRepositoryInterface $readRepo,
        protected CreateInventoryAction $createInventoryAction,
        protected UpdateInventoryAction $updateInventoryAction,
        protected ChangeInventoryStatusAction $changeStatusAction,
        protected AddInventoryFaceAction $addFaceAction,
        protected UpdatePricingAction $updatePricingAction,
        protected BlockInventoryAction $blockAction,
        protected UnblockInventoryAction $unblockAction,
        protected UploadInventoryDocumentAction $uploadDocAction,
        protected ListInventoryQuery $listQuery,
        protected SearchInventoryQuery $searchQuery,
        protected NearbyInventoryQuery $nearbyQuery,
        protected InventoryDashboardQuery $dashboardQuery,
        protected InventoryAvailabilityQuery $availabilityQuery
    ) {}

    /**
     * Get aggregate root details.
     */
    public function getDetails(string $id): Inventory
    {
        return $this->readRepo->findOrFail($id);
    }

    /**
     * Create aggregate root structure.
     */
    public function create(CreateInventoryData $data): Inventory
    {
        return $this->createInventoryAction->execute($data);
    }

    /**
     * Update parent aggregate specifications.
     */
    public function update(string $id, UpdateInventoryData $data): Inventory
    {
        return $this->updateInventoryAction->execute($id, $data);
    }

    /**
     * Transition aggregate operational status.
     */
    public function changeStatus(string $id, string $newStatus): Inventory
    {
        return $this->changeStatusAction->execute($id, $newStatus);
    }

    /**
     * Add face record to parent structure.
     */
    public function addFace(string $inventoryId, InventoryFaceData $data): InventoryFace
    {
        return $this->addFaceAction->execute($inventoryId, $data);
    }

    /**
     * Update pricing options on face.
     */
    public function updatePricing(string $faceId, InventoryPricingData $data): InventoryPricing
    {
        return $this->updatePricingAction->execute($faceId, $data);
    }

    /**
     * Block availability slot.
     */
    public function block(string $faceId, Carbon $startAt, Carbon $endAt, string $status, string $reason, ?string $remarks = null): InventoryAvailability
    {
        return $this->blockAction->execute($faceId, $startAt, $endAt, $status, $reason, $remarks);
    }

    /**
     * Unblock availability slot.
     */
    public function unblock(string $faceId, string $availabilityId): void
    {
        $this->unblockAction->execute($faceId, $availabilityId);
    }

    /**
     * Upload and attach compliance certificate file.
     */
    public function uploadDocument(string $inventoryId, UploadDocumentData $data): InventoryDocument
    {
        return $this->uploadDocAction->execute($inventoryId, $data);
    }

    /**
     * List paginated structures.
     */
    public function list(InventoryFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery->execute($filters, $perPage);
    }

    /**
     * Search structures.
     */
    public function search(InventoryFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->searchQuery->execute($filters, $perPage);
    }

    /**
     * Query nearby structures within radius boundary.
     */
    public function getNearby(float $lat, float $lon, float $radiusKm, int $limit = 50): Collection
    {
        return $this->nearbyQuery->execute($lat, $lon, $radiusKm, $limit);
    }

    /**
     * Compile aggregated workspace metrics.
     */
    public function getDashboard(): InventoryDashboardDTO
    {
        return $this->dashboardQuery->execute();
    }

    /**
     * Retrieve availability records of face.
     */
    public function getAvailability(string $faceId): Collection
    {
        return $this->availabilityQuery->execute($faceId);
    }
}
