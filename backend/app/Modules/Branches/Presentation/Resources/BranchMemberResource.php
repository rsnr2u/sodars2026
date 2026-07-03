<?php

declare(strict_types=1);

namespace App\Modules\Branches\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchMemberResource extends JsonResource
{
    /**
     * Transform branch user membership.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],
            'is_primary' => (bool) $this->is_primary,
            'is_active' => (bool) $this->is_active,
            'joined_at' => $this->joined_at?->toIso8601String(),
            'left_at' => $this->left_at?->toIso8601String(),
        ];
    }
}
