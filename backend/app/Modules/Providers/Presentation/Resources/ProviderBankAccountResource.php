<?php

declare(strict_types=1);

namespace App\Modules\Providers\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderBankAccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bank_name' => $this->bank_name,
            'account_holder' => $this->account_holder,
            'account_number' => $this->account_number,
            'routing_code' => $this->routing_code,
            'is_primary' => (bool) $this->is_primary,
            'verification_status' => $this->verification_status,
            'verified_by' => $this->verifier?->name,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'verification_reference' => $this->verification_reference,
        ];
    }
}
