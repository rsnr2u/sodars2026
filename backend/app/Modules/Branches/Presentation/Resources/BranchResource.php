<?php

declare(strict_types=1);

namespace App\Modules\Branches\Presentation\Resources;

use App\Modules\Branches\Domain\Enums\BranchStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'timezone' => $this->timezone,
            'currency_code' => $this->currency_code,
            'markup_percentage' => $this->markup_percentage,
            'support_email' => $this->support_email,
            'support_phone' => $this->support_phone,
            'status' => $this->status instanceof BranchStatus ? $this->status->value : (string) $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
