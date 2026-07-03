<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'inventory_face_id' => $this->inventory_face_id,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'daily_frequency' => $this->daily_frequency,
            
            'net_price_cents' => $this->net_price_cents,
            'markup_percentage' => $this->markup_percentage,
            'retail_price_cents' => $this->retail_price_cents,
            'total_item_price_cents' => $this->total_item_price_cents,
            
            'pricing_snapshot' => $this->pricing_snapshot,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
