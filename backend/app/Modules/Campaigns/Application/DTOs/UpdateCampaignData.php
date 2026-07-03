<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\DTOs;

use Illuminate\Http\Request;

class UpdateCampaignData
{
    /**
     * @param array<string, mixed>|null $objectives
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?array $objectives = null,
        public readonly ?int $budgetCents = null,
        public readonly ?string $bookingId = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            description: $request->input('description'),
            startDate: $request->input('start_date'),
            endDate: $request->input('end_date'),
            objectives: $request->input('objectives'),
            budgetCents: $request->input('budget_cents') ? (int) $request->input('budget_cents') : null,
            bookingId: $request->input('booking_id')
        );
    }
}
