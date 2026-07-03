<?php

declare(strict_types=1);

namespace App\Modules\CRM\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'source' => $this->source,
            'status' => $this->status ? $this->status->value : null,
            'lead_score' => $this->lead_score,
            'assigned_to' => $this->assigned_to,
            'account_id' => $this->account_id,
            'contact_id' => $this->contact_id,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
