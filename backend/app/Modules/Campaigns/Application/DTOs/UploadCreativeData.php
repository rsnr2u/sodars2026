<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\DTOs;

use Illuminate\Http\Request;

class UploadCreativeData
{
    public function __construct(
        public readonly string $fileName,
        public readonly string $filePath,
        public readonly string $fileType,
        public readonly ?int $fileSizeBytes = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            fileName: $request->input('file_name', basename($request->input('file_path'))),
            filePath: $request->input('file_path'),
            fileType: $request->input('file_type', pathinfo($request->input('file_path'), PATHINFO_EXTENSION)),
            fileSizeBytes: $request->input('file_size_bytes') ? (int) $request->input('file_size_bytes') : null
        );
    }
}
