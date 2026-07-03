<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\DTOs;

class CampaignDashboardDTO
{
    public function __construct(
        public readonly int $totalCampaigns,
        public readonly int $runningCampaigns,
        public readonly int $pausedCampaigns,
        public readonly int $pendingCreatives,
        public readonly int $totalBudgetCents,
        public readonly string $currency = 'INR'
    ) {}

    /**
     * @return array{total_campaigns: int, running_campaigns: int, paused_campaigns: int, pending_creatives: int, total_budget_cents: int, currency: string}
     */
    public function toArray(): array
    {
        return [
            'total_campaigns' => $this->totalCampaigns,
            'running_campaigns' => $this->runningCampaigns,
            'paused_campaigns' => $this->pausedCampaigns,
            'pending_creatives' => $this->pendingCreatives,
            'total_budget_cents' => $this->totalBudgetCents,
            'currency' => $this->currency,
        ];
    }
}
