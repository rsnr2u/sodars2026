<?php

declare(strict_types=1);

namespace App\Modules\Providers\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderContactResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'type' => $this->type instanceof \UnitEnum ? $this->type->value : $this->type,
        ];
    }
}
