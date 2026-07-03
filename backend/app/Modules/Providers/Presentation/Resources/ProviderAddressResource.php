<?php

declare(strict_types=1);

namespace App\Modules\Providers\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderAddressResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city?->name,
            'state' => $this->state?->name,
            'country' => $this->country?->name,
            'pincode' => $this->pincode?->code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_primary' => (bool) $this->is_primary,
        ];
    }
}
