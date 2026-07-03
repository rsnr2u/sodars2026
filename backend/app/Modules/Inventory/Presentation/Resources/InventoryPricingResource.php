<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryPricingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inventory_face_id' => $this->inventory_face_id,
            'pricing_type' => $this->pricing_type instanceof \UnitEnum ? $this->pricing_type->value : $this->pricing_type,
            'rate_cents' => $this->rate_cents,
            'currency' => $this->currency,
            'tax_inclusive' => $this->tax_inclusive,
            'minimum_booking_days' => $this->minimum_booking_days,
            'effective_from' => $this->effective_from,
            'effective_to' => $this->effective_to,
            'priority' => $this->priority,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
