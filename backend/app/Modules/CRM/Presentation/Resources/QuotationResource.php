<?php

declare(strict_types=1);

namespace App\Modules\CRM\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'opportunity_id' => $this->opportunity_id,
            'account_id' => $this->account_id,
            'quotation_number' => $this->quotation_number,
            'status' => $this->status ? $this->status->value : null,
            'active_version_number' => $this->active_version_number,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'active_version' => $this->whenLoaded('activeVersion', function () {
                return [
                    'id' => $this->activeVersion->id,
                    'version_number' => $this->activeVersion->version_number,
                    'valid_until' => $this->activeVersion->valid_until->toDateString(),
                    'subtotal_cents' => $this->activeVersion->subtotal_cents,
                    'discount_cents' => $this->activeVersion->discount_cents,
                    'tax_cents' => $this->activeVersion->tax_cents,
                    'grand_total_cents' => $this->activeVersion->grand_total_cents,
                    'currency' => $this->activeVersion->currency,
                    'items' => $this->activeVersion->items->map(fn($it) => [
                        'id' => $it->id,
                        'inventory_face_id' => $it->inventory_face_id,
                        'start_date' => $it->start_date->toDateString(),
                        'end_date' => $it->end_date->toDateString(),
                        'daily_frequency' => $it->daily_frequency,
                        'price_cents' => $it->price_cents,
                    ])->toArray(),
                ];
            }),
        ];
    }
}
