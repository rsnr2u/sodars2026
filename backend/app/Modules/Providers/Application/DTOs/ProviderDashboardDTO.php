<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\DTOs;

class ProviderDashboardDTO
{
    public function __construct(
        public readonly string $providerId,
        public readonly string $companyName,
        public readonly ?string $subscriptionPlan,
        public readonly int $documentsPending,
        public readonly int $staffCount,
        public readonly int $inventoryCount,
        public readonly float $revenue,
        public readonly int $pendingBookings
    ) {}

    /**
     * Convert the DTO to an array for JSON response serialization.
     */
    public function toArray(): array
    {
        return [
            'provider_id' => $this->providerId,
            'company_name' => $this->companyName,
            'subscription_plan' => $this->subscriptionPlan,
            'metrics' => [
                'documents_pending' => $this->documentsPending,
                'staff_count' => $this->staffCount,
                'inventory_count' => $this->inventoryCount,
                'revenue' => $this->revenue,
                'pending_bookings' => $this->pendingBookings,
            ],
        ];
    }
}
