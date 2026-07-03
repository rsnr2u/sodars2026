<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingPaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'paymentable_id' => $this->paymentable_id,
            'paymentable_type' => $this->paymentable_type,
            'payment_method' => $this->payment_method instanceof \UnitEnum ? $this->payment_method->value : $this->payment_method,
            'amount_cents' => $this->amount_cents,
            'reference_number' => $this->reference_number,
            'status' => $this->status instanceof \UnitEnum ? $this->status->value : $this->status,
            'recorded_by' => $this->recorded_by,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
