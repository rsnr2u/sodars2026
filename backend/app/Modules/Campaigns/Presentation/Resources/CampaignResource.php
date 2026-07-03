<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_code' => $this->campaign_code,
            'booking_id' => $this->booking_id,
            'customer_id' => $this->customer_id,
            'branch_id' => $this->branch_id,
            'name' => $this->name,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'status' => $this->status instanceof \UnitEnum ? $this->status->value : $this->status,
            'budget_cents' => $this->budget_cents,
            'currency' => $this->currency,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
