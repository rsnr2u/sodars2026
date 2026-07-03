<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Queries;

use App\Modules\Inventory\Application\DTOs\InventoryDashboardDTO;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Inventory\Domain\Entities\InventoryAvailability;
use App\Modules\Inventory\Domain\Entities\InventoryPricing;

class InventoryDashboardQuery
{
    /**
     * Retrieve aggregated statistics for structure dashboards.
     */
    public function execute(): InventoryDashboardDTO
    {
        $totalStructures = Inventory::count();
        $totalFaces = InventoryFace::count();
        $activeFaces = InventoryFace::where('is_active', true)->count();
        $marketplaceEnabledCount = Inventory::where('marketplace_enabled', true)->count();

        $reservedCount = InventoryAvailability::where('availability_status', 'reserved')->count();
        $totalAvail = InventoryAvailability::count();
        $occupancyRate = $totalAvail > 0 ? (float) (($reservedCount / $totalAvail) * 100.0) : 0.0;

        // Daily potential rate sum in cents
        $revenuePotentialCents = (int) InventoryPricing::sum('rate_cents');

        $digitalCount = Inventory::where('inventory_category', 'Digital')->count();
        $staticCount = Inventory::where('inventory_category', 'Static')->count();
        $maintenanceCount = InventoryFace::whereHas('availabilities', function ($q) {
            $q->where('availability_status', 'maintenance');
        })->count();

        $pendingApprovalCount = Inventory::where('status', 'pending_approval')->count();

        return new InventoryDashboardDTO(
            totalStructures: $totalStructures,
            totalFaces: $totalFaces,
            activeFaces: $activeFaces,
            marketplaceEnabledCount: $marketplaceEnabledCount,
            occupancyRate: $occupancyRate,
            revenuePotentialCents: $revenuePotentialCents,
            digitalCount: $digitalCount,
            staticCount: $staticCount,
            maintenanceCount: $maintenanceCount,
            pendingApprovalCount: $pendingApprovalCount
        );
    }
}
