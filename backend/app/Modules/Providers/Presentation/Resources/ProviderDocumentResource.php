<?php

declare(strict_types=1);

namespace App\Modules\Providers\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $media = $this->media->first();
        return [
            'id' => $this->id,
            'document_type' => $this->document_type,
            'status' => $this->status instanceof \UnitEnum ? $this->status->value : $this->status,
            'version' => (int) $this->version,
            'is_current' => (bool) $this->is_current,
            'remarks' => $this->remarks,
            'file_name' => $media?->file_name,
            'file_path' => $media?->file_path,
            'verified_by' => $this->verifier?->name,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
        ];
    }
}
