<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Queries;

use App\Modules\Branches\Domain\Entities\BranchCoverageArea;
use App\Modules\Branches\Domain\Entities\BranchUser;

class BranchDashboardQuery
{
    /**
     * Retrieve aggregated metrics for the branch dashboard.
     */
    public function execute(string $branchId): array
    {
        $memberCount = BranchUser::where('branch_id', $branchId)->count();
        $activeMemberCount = BranchUser::where('branch_id', $branchId)->where('is_active', true)->count();
        $coverageCount = BranchCoverageArea::where('branch_id', $branchId)->count();

        return [
            'branch_id' => $branchId,
            'metrics' => [
                'total_members' => $memberCount,
                'active_members' => $activeMemberCount,
                'coverage_areas_count' => $coverageCount,
                'active_providers_count' => 0,
                'active_campaigns_count' => 0,
                'gross_bookings_amount' => 0.0,
                'occupancy_rate_percentage' => 0.0,
            ],
        ];
    }
}
