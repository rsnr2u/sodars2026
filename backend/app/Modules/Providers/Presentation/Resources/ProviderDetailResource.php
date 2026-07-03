<?php

declare(strict_types=1);

namespace App\Modules\Providers\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'registration_number' => $this->registration_number,
            'provider_code' => $this->provider_code,
            'default_branch_id' => $this->default_branch_id,
            'status' => $this->status instanceof \UnitEnum ? $this->status->value : $this->status,
            'preferred_payout_method' => $this->preferred_payout_method,
            'external_reference' => $this->external_reference,
            'legacy_reference' => $this->legacy_reference,
            'created_at' => $this->created_at?->toIso8601String(),
            'addresses' => ProviderAddressResource::collection($this->whenLoaded('addresses')),
            'contacts' => ProviderContactResource::collection($this->whenLoaded('contacts')),
            'documents' => ProviderDocumentResource::collection($this->whenLoaded('documents')),
            'staff' => ProviderMemberResource::collection($this->whenLoaded('staff')),
            'bank_accounts' => ProviderBankAccountResource::collection($this->whenLoaded('bankAccounts')),
            'settings' => $this->relationLoaded('settings') && $this->settings ? [
                'marketplace_enabled' => $this->settings->settings->marketplaceEnabled,
                'booking_notifications' => $this->settings->settings->bookingNotifications,
                'email' => $this->settings->settings->email,
                'sms' => $this->settings->settings->sms,
            ] : null,
        ];
    }
}
