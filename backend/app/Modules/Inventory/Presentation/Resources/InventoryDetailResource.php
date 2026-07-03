<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inventory_code' => $this->inventory_code,
            'display_name' => $this->display_name,
            'provider_id' => $this->provider_id,
            'branch_id' => $this->branch_id,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'district_id' => $this->district_id,
            'city_id' => $this->city_id,
            'pincode_id' => $this->pincode_id,
            'inventory_category' => $this->inventory_category,
            'inventory_type' => $this->inventory_type,
            'ownership_type' => $this->ownership_type,
            'status' => $this->status instanceof \UnitEnum ? $this->status->value : $this->status,
            'marketplace_enabled' => $this->marketplace_enabled,
            'is_featured' => $this->is_featured,
            'accepts_programmatic_booking' => $this->accepts_programmatic_booking,
            'visibility' => $this->visibility,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'geo_hash' => $this->geo_hash,
            'normalized_address' => $this->normalized_address,
            'search_keywords' => $this->search_keywords,
            'inventory_capabilities' => $this->inventory_capabilities,
            'ai_scores' => $this->ai_scores,
            'faces' => InventoryFaceResource::collection($this->whenLoaded('faces')),
            'documents' => $this->whenLoaded('documents'),
            'media' => $this->whenLoaded('inventoryMedia'),
            'activities' => $this->whenLoaded('activities'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
