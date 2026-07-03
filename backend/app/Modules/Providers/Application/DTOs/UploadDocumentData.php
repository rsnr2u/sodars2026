<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\DTOs;

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
            documentType: $request->input('document_type'),
            filePath: $request->input('file_path')
        );
    }
}
