<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'grand_total_cents' => $this->grand_total_cents,
            'currency' => $this->currency,
            'status' => $this->status instanceof \UnitEnum ? $this->status->value : $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
