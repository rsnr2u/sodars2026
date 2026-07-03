<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignProofResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'inventory_face_id' => $this->inventory_face_id,
            'file_path' => $this->file_path,
            'notes' => $this->notes,
            'uploaded_by' => $this->uploaded_by,
            'status' => $this->status instanceof \UnitEnum ? $this->status->value : $this->status,
            'verified_by' => $this->verified_by,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
