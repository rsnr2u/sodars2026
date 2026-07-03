<?php

declare(strict_types=1);

namespace App\Platform\DAM\Presentation\Resources;

use App\Platform\DAM\Domain\Entities\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        /** @var Asset $this */
        $currentFile = $this->currentVersion?->file;
        $publicUrl = null;
        if ($currentFile) {
            $storage = app(\App\Platform\DAM\Domain\Contracts\StorageProvider::class);
            $publicUrl = $storage->getUrl($currentFile->path);
        }

        // Map conversions
        $conversions = [];
        if ($this->relationLoaded('currentVersion') && $this->currentVersion && $this->currentVersion->relationLoaded('conversions')) {
            foreach ($this->currentVersion->conversions as $conv) {
                $storage = app(\App\Platform\DAM\Domain\Contracts\StorageProvider::class);
                $conversions[$conv->conversion_name] = [
                    'id' => $conv->id,
                    'file_id' => $conv->file_id,
                    'path' => $conv->file?->path,
                    'url' => $conv->file ? $storage->getUrl($conv->file->path) : null,
                    'mime_type' => $conv->file?->mime_type,
                    'file_size' => $conv->file?->file_size,
                    'width' => $conv->file?->width,
                    'height' => $conv->file?->height,
                ];
            }
        }

        return [
            'id' => $this->id,
            'folder_id' => $this->folder_id,
            'current_version_id' => $this->current_version_id,
            'title' => $this->title,
            'description' => $this->description,
            'asset_type' => $this->asset_type->value,
            'status' => $this->status->value,
            'attachment_count' => $this->attachment_count,
            'download_count' => $this->download_count,
            'view_count' => $this->view_count,
            'archived_at' => $this->archived_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Current version file metadata
            'file' => $currentFile ? [
                'id' => $currentFile->id,
                'storage_provider' => $currentFile->storage_provider,
                'disk' => $currentFile->disk,
                'path' => $currentFile->path,
                'url' => $publicUrl,
                'mime_type' => $currentFile->mime_type,
                'file_size' => $currentFile->file_size,
                'width' => $currentFile->width,
                'height' => $currentFile->height,
                'orientation' => $currentFile->orientation,
                'checksum_sha256' => $currentFile->checksum_sha256,
            ] : null,

            // Thumbnail / Optimized images
            'conversions' => $conversions,

            // Folder details if loaded
            'folder' => $this->whenLoaded('folder', function () {
                return [
                    'id' => $this->folder->id,
                    'name' => $this->folder->name,
                ];
            }),
        ];
    }
}
