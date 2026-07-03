<?php

declare(strict_types=1);

namespace App\Modules\Branches\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchCoverageAreaResource extends JsonResource
{
    /**
     * Transform coverage details with eager loaded entity names.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'country' => [
                'id' => $this->country_id,
                'name' => $this->country?->name,
            ],
            'state' => [
                'id' => $this->state_id,
                'name' => $this->state?->name,
            ],
            'district' => $this->district_id ? [
                'id' => $this->district_id,
                'name' => $this->district?->name,
            ] : null,
            'city' => [
                'id' => $this->city_id,
                'name' => $this->city?->name,
            ],
        ];
    }
}
