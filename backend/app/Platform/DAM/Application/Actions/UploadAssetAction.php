<?php

declare(strict_types=1);

namespace App\Platform\DAM\Application\Actions;

use App\Platform\DAM\Application\Jobs\ProcessAssetConversions;
use App\Platform\DAM\Domain\Contracts\StorageProvider;
use App\Platform\DAM\Domain\Entities\Asset;
use App\Platform\DAM\Domain\Entities\AssetVersion;
use App\Platform\DAM\Domain\Entities\StoredFile;
use App\Platform\DAM\Domain\Enums\AssetStatus;
use App\Platform\DAM\Domain\Enums\AssetType;
use App\Platform\DAM\Infrastructure\Metadata\MetadataExtractor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class UploadAssetAction
{
    protected StorageProvider $storage;
    protected MetadataExtractor $extractor;

    public function __construct(StorageProvider $storage, MetadataExtractor $extractor)
    {
        $this->storage = $storage;
        $this->extractor = $extractor;
    }

    public function execute(
        UploadedFile $file,
        string $title,
        ?string $description = null,
        ?string $folderId = null
    ): Asset {
        return DB::transaction(function () use ($file, $title, $description, $folderId) {
            // 1. Extract file metadata and hashes
            $meta = $this->extractor->extract($file);

            // 2. Determine AssetType enum value
            $mime = $meta['mime_type'];
            $assetType = AssetType::OTHER;
            if (str_starts_with($mime, 'image/')) {
                $assetType = AssetType::IMAGE;
            } elseif (str_starts_with($mime, 'video/')) {
                $assetType = AssetType::VIDEO;
            } elseif (str_starts_with($mime, 'audio/')) {
                $assetType = AssetType::AUDIO;
            } elseif ($mime === 'application/pdf' || str_contains($mime, 'word') || str_contains($mime, 'excel') || str_contains($mime, 'powerpoint') || str_contains($mime, 'officedocument') || $mime === 'text/plain') {
                $assetType = AssetType::DOCUMENT;
            } elseif ($mime === 'application/zip' || $mime === 'application/x-rar-compressed' || $mime === 'application/x-tar') {
                $assetType = AssetType::ARCHIVE;
            }

            // 3. Upload physical original file
            $destinationDirectory = 'dam/originals/' . date('Y/m');
            $storedPath = $this->storage->store($file, $destinationDirectory);

            // 4. Create physical StoredFile record
            $storedFile = StoredFile::create([
                'id' => (string) Str::uuid(),
                'storage_provider' => 'local',
                'disk' => 'public',
                'path' => $storedPath,
                'checksum_sha256' => $meta['checksum_sha256'],
                'checksum_md5' => $meta['checksum_md5'],
                'mime_type' => $mime,
                'file_size' => $meta['file_size'],
                'width' => $meta['width'],
                'height' => $meta['height'],
                'duration' => $meta['duration'],
                'pages' => $meta['pages'],
                'dpi' => $meta['dpi'],
                'orientation' => $meta['orientation'],
                'metadata' => $meta['metadata'],
            ]);

            // 5. Create Asset header
            $asset = Asset::create([
                'id' => (string) Str::uuid(),
                'folder_id' => $folderId,
                'current_version_id' => null,
                'title' => $title,
                'description' => $description,
                'asset_type' => $assetType,
                'status' => AssetStatus::UPLOADING,
            ]);

            // 6. Create active version (v1)
            $version = AssetVersion::create([
                'id' => (string) Str::uuid(),
                'asset_id' => $asset->id,
                'file_id' => $storedFile->id,
                'version_number' => 1,
            ]);

            // 7. Update current version pointer on Asset and change status to processing
            $asset->update([
                'current_version_id' => $version->id,
                'status' => AssetStatus::PROCESSING,
            ]);

            // 8. Dispatch Conversions Background Job
            ProcessAssetConversions::dispatch($asset->id, $version->id);

            return $asset;
        });
    }
}
