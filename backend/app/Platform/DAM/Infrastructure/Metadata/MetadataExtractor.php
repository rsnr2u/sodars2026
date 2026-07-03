<?php

declare(strict_types=1);

namespace App\Platform\DAM\Infrastructure\Metadata;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class MetadataExtractor
{
    /**
     * Extract full metrics and dimensions from uploaded file binary.
     */
    public function extract(UploadedFile $file): array
    {
        $realPath = $file->getRealPath();

        $sha256 = hash_file('sha256', $realPath);
        $md5 = hash_file('md5', $realPath);
        $mime = $file->getMimeType() ?? $file->getClientMimeType() ?? 'application/octet-stream';
        $size = $file->getSize();

        $width = null;
        $height = null;
        $orientation = null;

        // If it is an image, extract dimensions
        if (str_starts_with($mime, 'image/')) {
            $imageInfo = @getimagesize($realPath);
            if ($imageInfo !== false) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
                if ($width > $height) {
                    $orientation = 'landscape';
                } elseif ($height > $width) {
                    $orientation = 'portrait';
                } else {
                    $orientation = 'square';
                }
            }
        }

        return [
            'checksum_sha256' => $sha256,
            'checksum_md5' => $md5,
            'mime_type' => $mime,
            'file_size' => $size,
            'width' => $width,
            'height' => $height,
            'orientation' => $orientation,
            'duration' => null,
            'pages' => null,
            'dpi' => null,
            'metadata' => [
                'original_name' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
            ]
        ];
    }
}
