<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryFaceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inventory_id' => $this->inventory_id,
            'face_code' => $this->face_code,
            'display_name' => $this->display_name,
            'facing_direction' => $this->facing_direction instanceof \UnitEnum ? $this->facing_direction->value : $this->facing_direction,
            'display_order' => $this->display_order,
            'physical_specifications' => $this->physical_specifications,
            'is_active' => $this->is_active,
            'pricing' => InventoryPricingResource::collection($this->whenLoaded('pricing')),
            'availabilities' => InventoryAvailabilityResource::collection($this->whenLoaded('availabilities')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
