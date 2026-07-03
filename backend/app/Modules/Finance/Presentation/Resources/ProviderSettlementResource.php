<?php

declare(strict_types=1);

namespace App\Modules\Finance\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderSettlementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'settlement_number' => $this->settlement_number,
            'provider_id' => $this->provider_id,
            'booking_id' => $this->booking_id,
            'invoice_id' => $this->invoice_id,
            'total_amount_cents' => $this->total_amount_cents,
            'provider_share_cents' => $this->provider_share_cents,
            'commission_cents' => $this->commission_cents,
            'tax_cents' => $this->tax_cents,
            'status' => $this->status instanceof \UnitEnum ? $this->status->value : $this->status,
            'processed_at' => $this->processed_at?->toIso8601String(),
            'payout_reference' => $this->payout_reference,
            
            'items' => $this->whenLoaded('items'),
            'adjustments' => $this->whenLoaded('adjustments'),
            'activities' => $this->whenLoaded('activities'),
            
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
