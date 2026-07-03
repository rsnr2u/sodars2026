<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\DTOs;

use Illuminate\Http\Request;

class CreateCampaignData
{
    /**
     * @param array<int, string> $inventoryFaceIds
     * @param array<string, mixed>|null $objectives
     */
    public function __construct(
        public readonly string $name,
        public readonly string $customerId,
        public readonly string $branchId,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly array $inventoryFaceIds,
        public readonly ?string $bookingId = null,
        public readonly ?string $description = null,
        public readonly ?array $objectives = null,
        public readonly ?int $budgetCents = null,
        public readonly string $currency = 'INR'
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            customerId: $request->input('customer_id'),
            branchId: $request->input('branch_id'),
            startDate: $request->input('start_date'),
            endDate: $request->input('end_date'),
            inventoryFaceIds: $request->input('inventory_face_ids', []),
            bookingId: $request->input('booking_id'),
            description: $request->input('description'),
            objectives: $request->input('objectives'),
            budgetCents: $request->input('budget_cents') ? (int) $request->input('budget_cents') : null,
            currency: $request->input('currency', 'INR')
        );
    }
}
