<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\DTOs;

class InventoryDashboardDTO
{
    public function __construct(
        public readonly int $totalStructures,
        public readonly int $totalFaces,
        public readonly int $activeFaces,
        public readonly int $marketplaceEnabledCount,
        public readonly float $occupancyRate,
        public readonly int $revenuePotentialCents,
        public readonly int $digitalCount,
        public readonly int $staticCount,
        public readonly int $maintenanceCount,
        public readonly int $pendingApprovalCount
    ) {}

    /**
     * Map DTO to standard array output.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total_structures' => $this->totalStructures,
            'total_faces' => $this->totalFaces,
            'active_faces' => $this->activeFaces,
            'marketplace_enabled_count' => $this->marketplaceEnabledCount,
            'occupancy_rate' => $this->occupancyRate,
            'revenue_potential_cents' => $this->revenuePotentialCents,
            'digital_count' => $this->digitalCount,
            'static_count' => $this->staticCount,
            'maintenance_count' => $this->maintenanceCount,
            'pending_approval_count' => $this->pendingApprovalCount,
        ];
    }
}
