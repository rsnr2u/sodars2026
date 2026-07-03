<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_code' => $this->booking_code,
            'customer_id' => $this->customer_id,
            'branch_id' => $this->branch_id,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            
            // Financial Summaries
            'subtotal_cents' => $this->subtotal_cents,
            'discount_cents' => $this->discount_cents,
            'tax_cents' => $this->tax_cents,
            'platform_fee_cents' => $this->platform_fee_cents,
            'provider_share_cents' => $this->provider_share_cents,
            'commission_cents' => $this->commission_cents,
            'grand_total_cents' => $this->grand_total_cents,
            'currency' => $this->currency,

            'status' => $this->status instanceof \UnitEnum ? $this->status->value : $this->status,
            
            'items' => BookingItemResource::collection($this->whenLoaded('items')),
            'payments' => BookingPaymentResource::collection($this->whenLoaded('payments')),
            'status_history' => $this->whenLoaded('statusHistory'),
            'notes' => $this->whenLoaded('notes'),
            'activities' => $this->whenLoaded('activities'),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
