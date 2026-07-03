<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryAvailabilityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inventory_face_id' => $this->inventory_face_id,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'availability_status' => $this->availability_status instanceof \UnitEnum ? $this->availability_status->value : $this->availability_status,
            'reason' => $this->reason,
            'source' => $this->source,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
