<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\DTOs;

use Illuminate\Http\Request;

class UploadProofData
{
    public function __construct(
        public readonly string $filePath,
        public readonly ?string $inventoryFaceId = null,
        public readonly ?string $notes = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            filePath: $request->input('file_path'),
            inventoryFaceId: $request->input('inventory_face_id'),
            notes: $request->input('notes')
        );
    }
}
