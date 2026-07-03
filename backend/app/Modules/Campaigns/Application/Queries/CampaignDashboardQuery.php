<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Queries;

use App\Modules\Campaigns\Application\DTOs\CampaignDashboardDTO;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignCreative;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;
use App\Modules\Campaigns\Domain\Enums\CreativeStatus;

class CampaignDashboardQuery
{
    public function execute(?string $customerId = null): CampaignDashboardDTO
    {
        $campaignQuery = Campaign::query();
        $creativeQuery = CampaignCreative::query();

        if ($customerId) {
            $campaignQuery->where('customer_id', $customerId);
            $creativeQuery->whereHas('campaign', function ($q) use ($customerId) {
                $q->where('customer_id', $customerId);
            });
        }

        $totalCampaigns = $campaignQuery->count();
        $runningCampaigns = (clone $campaignQuery)->where('status', CampaignStatus::Running->value)->count();
        $pausedCampaigns = (clone $campaignQuery)->where('status', CampaignStatus::Paused->value)->count();

        $pendingCreatives = $creativeQuery->where('status', CreativeStatus::Pending->value)->count();

        $totalBudgetCents = (int) $campaignQuery->sum('budget_cents');

        return new CampaignDashboardDTO(
            totalCampaigns: $totalCampaigns,
            runningCampaigns: $runningCampaigns,
            pausedCampaigns: $pausedCampaigns,
            pendingCreatives: $pendingCreatives,
            totalBudgetCents: $totalBudgetCents
        );
    }
}
