<?php

declare(strict_types=1);

namespace App\Modules\Finance\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'booking_id' => $this->booking_id,
            'customer_id' => $this->customer_id,
            'branch_id' => $this->branch_id,
            'issue_date' => $this->issue_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'subtotal_cents' => $this->subtotal_cents,
            'discount_cents' => $this->discount_cents,
            'tax_cents' => $this->tax_cents,
            'grand_total_cents' => $this->grand_total_cents,
            'currency' => $this->currency,
            'status' => $this->status instanceof \UnitEnum ? $this->status->value : $this->status,
            'invoice_type' => $this->invoice_type instanceof \UnitEnum ? $this->invoice_type->value : $this->invoice_type,
            'booking_snapshot' => $this->booking_snapshot,
            
            'items' => $this->whenLoaded('items'),
            'adjustments' => $this->whenLoaded('adjustments'),
            'taxes' => $this->whenLoaded('taxes'),
            'activities' => $this->whenLoaded('activities'),
            
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
