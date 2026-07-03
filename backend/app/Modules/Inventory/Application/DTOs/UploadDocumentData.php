<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\DTOs;

use Illuminate\Http\Request;

class UploadDocumentData
{
    public function __construct(
        public readonly string $documentType,
        public readonly string $filePath
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            documentType: (string) $request->input('document_type'),
            filePath: (string) $request->input('file_path')
        );
    }
}
